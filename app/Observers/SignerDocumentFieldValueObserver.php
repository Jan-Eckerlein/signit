<?php

namespace App\Observers;

use App\Enums\DocumentStatus;
use App\Jobs\SendDocumentCompletedNotification;
use App\Jobs\SendDocumentCompletedMagicLinkNotification;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\SignerDocumentFieldValue;
use App\Services\MagicLinkService;
use App\Enums\Icon;
use Illuminate\Support\Facades\Log;

class SignerDocumentFieldValueObserver
{
    public function created(SignerDocumentFieldValue $signerDocumentFieldValue): void
    {
        $this->checkAndUpdateDocumentStatus($signerDocumentFieldValue);
    }

    public function updated(SignerDocumentFieldValue $signerDocumentFieldValue): void
    {
        $this->checkAndUpdateDocumentStatus($signerDocumentFieldValue);
    }

    private function checkAndUpdateDocumentStatus(SignerDocumentFieldValue $signerDocumentFieldValue): void
    {
        $document = $signerDocumentFieldValue->signerDocumentField->documentSigner->document;
        
        if ($document->areAllFieldsCompleted()) {
            // Update document status
            $document->status = DocumentStatus::COMPLETED;
            $document->save();
            
            // Create audit log
            DocumentLog::create([
                'document_id' => $document->id,
                'document_signer_id' => $signerDocumentFieldValue->signerDocumentField->document_signer_id,
                'ip' => request()->ip(),
                'date' => now(),
                'icon' => Icon::CHECKMARK,
                'text' => "Document completed by {$signerDocumentFieldValue->signerDocumentField->documentSigner->user->name}",
            ]);
            
            // Send notifications to all parties
            $this->sendCompletionNotifications($document);
            
            Log::info('Document completed', [
                'document_id' => $document->id,
                'completed_by_user_id' => $signerDocumentFieldValue->signerDocumentField->documentSigner->user_id,
            ]);
        }
    }
    
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