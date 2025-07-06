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
        // Log the field value creation
        $this->logFieldValueCreation($signerDocumentFieldValue);
        
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
            $log = DocumentLog::create([
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
				'log_id' => $log->id,
                'document_id' => $document->id,
                'completed_by_user_id' => $signerDocumentFieldValue->signerDocumentField->documentSigner->user_id,
            ]);
        }
    }
    
    private function logFieldValueCreation(SignerDocumentFieldValue $signerDocumentFieldValue): void
    {
        $field = $signerDocumentFieldValue->signerDocumentField;
        $documentSigner = $field->documentSigner;
        $document = $documentSigner->document;
        $user = $documentSigner->user;
        
        // Create audit log for field value creation
        $log = DocumentLog::create([
            'document_id' => $document->id,
            'document_signer_id' => $documentSigner->id,
            'ip' => request()->ip(),
            'date' => now(),
            'icon' => Icon::WATCH,
            'text' => "Field '{$field->label}' completed by {$user->name}",
        ]);
        
        Log::info('Field value created', [
			'log_id' => $log->id,
            'document_id' => $document->id,
            'document_signer_id' => $documentSigner->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'field_id' => $field->id,
            'field_label' => $field->label,
            'field_type' => $field->type->value,
            'value_type' => $this->getValueType($signerDocumentFieldValue),
        ]);
    }
    
    private function getValueType(SignerDocumentFieldValue $signerDocumentFieldValue): string
    {
        if ($signerDocumentFieldValue->value_signature_sign_id) {
            return 'signature';
        } elseif ($signerDocumentFieldValue->value_initials) {
            return 'initials';
        } elseif ($signerDocumentFieldValue->value_text) {
            return 'text';
        } elseif ($signerDocumentFieldValue->value_checkbox) {
            return 'checkbox';
        } elseif ($signerDocumentFieldValue->value_date) {
            return 'date';
        }
        
        return 'unknown';
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