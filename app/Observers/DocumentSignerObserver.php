<?php

namespace App\Observers;

use App\Enums\DocumentStatus;
use App\Enums\Icon;
use App\Jobs\SendDocumentCompletedNotification;
use App\Jobs\SendDocumentCompletedMagicLinkNotification;
use App\Jobs\SendFirstSignatureNotification;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentSigner;
use App\Services\MagicLinkService;
use Illuminate\Support\Facades\Log;

class DocumentSignerObserver
{
    /**
     * Handle the DocumentSigner "created" event.
     */
    public function created(DocumentSigner $documentSigner): void
    {
        //
    }

    /**
     * Handle the DocumentSigner "updated" event.
     */
    public function updated(DocumentSigner $documentSigner): void
    {
        // Check if signature completion fields were just set
        if ($documentSigner->wasChanged('signature_completed_at') && $documentSigner->signature_completed_at !== null) {
            $this->handleSignatureCompletion($documentSigner);
        }
    }

    /**
     * Handle the DocumentSigner "deleted" event.
     */
    public function deleted(DocumentSigner $documentSigner): void
    {
        //
    }

    /**
     * Handle the DocumentSigner "restored" event.
     */
    public function restored(DocumentSigner $documentSigner): void
    {
        //
    }

    /**
     * Handle the DocumentSigner "force deleted" event.
     */
    public function forceDeleted(DocumentSigner $documentSigner): void
    {
        //
    }

    /**
     * Handle signature completion logic
     */
    private function handleSignatureCompletion(DocumentSigner $documentSigner): void
    {
        $document = $documentSigner->document;
        
        // Log the signature completion
        $this->logSignatureCompletion($documentSigner);
        
        // Check if this is the first signature for the document
        if ($this->isFirstSignature($document)) {
            $this->handleFirstSignature($document, $documentSigner);
        }
        
        // Check if all signers are now completed
        if ($this->areAllSignersCompleted($document)) {
            $this->handleDocumentCompletion($document, $documentSigner);
        }
    }

    /**
     * Check if this is the first signature for the document
     */
    private function isFirstSignature(Document $document): bool
    {
        return $document->documentSigners()
            ->whereNotNull('signature_completed_at')
            ->count() === 1;
    }

    /**
     * Check if all signers have completed their signatures
     */
    private function areAllSignersCompleted(Document $document): bool
    {
        return $document->documentSigners()
            ->whereNull('signature_completed_at')
            ->doesntExist();
    }

    /**
     * Handle first signature logic
     */
    private function handleFirstSignature(Document $document, DocumentSigner $documentSigner): void
    {
        // Update document status to IN_PROGRESS when first signature is placed
        $document->update(['status' => DocumentStatus::IN_PROGRESS]);
        
        // Create audit log
        DocumentLog::create([
            'document_id' => $document->id,
            'document_signer_id' => $documentSigner->id,
            'ip' => request()->ip(),
            'date' => now(),
            'icon' => Icon::SEND,
            'text' => "First signature completed by {$documentSigner->user->name}",
        ]);
        
        // Send notification to document owner
        if ($document->ownerUser) {
            SendFirstSignatureNotification::dispatch($document);
        }
        
        Log::info('First signature completed', [
            'document_id' => $document->id,
            'signer_id' => $documentSigner->id,
            'user_id' => $documentSigner->user_id,
        ]);
    }

    /**
     * Handle document completion logic
     */
    private function handleDocumentCompletion(Document $document, DocumentSigner $documentSigner): void
    {
        // Update document status
        $document->update(['status' => DocumentStatus::COMPLETED]);
        
        // Create audit log
        DocumentLog::create([
            'document_id' => $document->id,
            'document_signer_id' => $documentSigner->id,
            'ip' => request()->ip(),
            'date' => now(),
            'icon' => Icon::CHECKMARK,
            'text' => "Document completed by {$documentSigner->user->name}",
        ]);
        
        // Send completion notifications
        $this->sendCompletionNotifications($document);
        
        Log::info('Document completed', [
            'document_id' => $document->id,
            'completed_by_signer_id' => $documentSigner->id,
            'user_id' => $documentSigner->user_id,
        ]);
    }

    /**
     * Log signature completion
     */
    private function logSignatureCompletion(DocumentSigner $documentSigner): void
    {
        $document = $documentSigner->document;
        $user = $documentSigner->user;
        
        DocumentLog::create([
            'document_id' => $document->id,
            'document_signer_id' => $documentSigner->id,
            'ip' => request()->ip(),
            'date' => now(),
            'icon' => Icon::CHECKMARK,
            'text' => "Signature completed by {$user->name}",
        ]);
    }

    /**
     * Send completion notifications to all parties
     */
    private function sendCompletionNotifications(Document $document): void
    {
        // Load all signers with their users
        $document->load('documentSigners.user', 'ownerUser');
        $magicLinkService = new MagicLinkService();
        
        // Send notification to document owner
        if ($document->ownerUser) {
            SendDocumentCompletedNotification::dispatch($document, $document->ownerUser);
        }
        
        // Send notifications to all signers (including anonymous users)
        foreach ($document->documentSigners as $documentSigner) {
            if ($documentSigner->user && $documentSigner->user->id !== $document->owner_user_id) {
                if ($documentSigner->user->isAnonymous()) {
                    // For anonymous users, create magic link and send notification
                    $token = $magicLinkService->createMagicLink($documentSigner->user, $document);
                    SendDocumentCompletedMagicLinkNotification::dispatch($document, $documentSigner->user, $token);
                } else {
                    // For regular users, send standard email notification
                    SendDocumentCompletedNotification::dispatch($document, $documentSigner->user);
                }
            }
        }
    }
}
