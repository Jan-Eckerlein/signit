<?php

namespace App\Observers;

use App\Enums\DocumentStatus;
use App\Models\SignerDocumentFieldValue;

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
            $document->status = DocumentStatus::COMPLETED;
            $document->save();
        }
    }
} 