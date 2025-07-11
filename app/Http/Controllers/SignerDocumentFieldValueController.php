<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreSignerDocumentFieldValueRequest;
use App\Http\Resources\SignerDocumentFieldValueResource;
use App\Models\SignerDocumentFieldValue;
use Illuminate\Support\Facades\Gate;

class SignerDocumentFieldValueController extends Controller
{
    /**
     * @group Signer Document Field Values
     * @title "Create Signer Document Field Value"
     * @description "Create a new signer document field value"
     * Store a newly created resource in storage.
     */
    public function store(StoreSignerDocumentFieldValueRequest $request): SignerDocumentFieldValueResource
    {
        Gate::authorize('create', SignerDocumentFieldValue::class);
        $signerDocumentFieldValue = SignerDocumentFieldValue::create($request->validated());
        
        // The observer has already run and potentially updated the document status
        $document = $signerDocumentFieldValue->signerDocumentField->documentSigner->document;
        $wasCompleted = $document->status === DocumentStatus::COMPLETED;
        
        return new SignerDocumentFieldValueResource($signerDocumentFieldValue->load(['signatureSign']), [
            'document_completed' => $wasCompleted,
            'document_status' => $document->status
        ]);
    }
}