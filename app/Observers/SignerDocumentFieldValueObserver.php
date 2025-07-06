<?php

namespace App\Observers;

use App\Enums\Icon;
use App\Models\DocumentLog;
use App\Models\SignerDocumentFieldValue;
use Illuminate\Support\Facades\Log;

class SignerDocumentFieldValueObserver
{
    public function created(SignerDocumentFieldValue $signerDocumentFieldValue): void
    {
        // Log the field value creation
        $this->logFieldValueCreation($signerDocumentFieldValue);

        // Field value updates are logged but don't trigger document completion
        // Document completion is now handled by DocumentSignerObserver
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
} 