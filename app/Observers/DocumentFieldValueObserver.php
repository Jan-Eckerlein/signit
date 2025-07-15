<?php

namespace App\Observers;

use App\Enums\Icon;
use App\Models\DocumentLog;
use App\Models\DocumentFieldValue;
use Illuminate\Support\Facades\Log;

class DocumentFieldValueObserver
{
    public function created(DocumentFieldValue $documentFieldValue): void
    {
        // Log the field value creation
        $this->logFieldValueCreation($documentFieldValue);

        // Field value updates are logged but don't trigger document completion
        // Document completion is now handled by DocumentSignerObserver
    }

    private function logFieldValueCreation(DocumentFieldValue $documentFieldValue): void
    {
        $field = $documentFieldValue->documentField;
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
            'value_type' => $this->getValueType($documentFieldValue),
        ]);
    }
    
    private function getValueType(DocumentFieldValue $documentFieldValue): string
    {
        if ($documentFieldValue->value_signature_sign_id) {
            return 'signature';
        } elseif ($documentFieldValue->value_initials) {
            return 'initials';
        } elseif ($documentFieldValue->value_text) {
            return 'text';
        } elseif ($documentFieldValue->value_checkbox) {
            return 'checkbox';
        } elseif ($documentFieldValue->value_date) {
            return 'date';
        }
        
        return 'unknown';
    }
} 