<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreDocumentFieldValueRequest;
use App\Http\Resources\DocumentFieldValueResource;
use App\Models\DocumentFieldValue;
use Illuminate\Support\Facades\Gate;

/**
 * @group Signer Document Field Values
 */
class DocumentFieldValueController extends Controller
{
    /**
     * Create Signer Document Field Value
     * 
     * Store a newly created signer document field value in storage.
     */
    public function store(StoreDocumentFieldValueRequest $request): DocumentFieldValueResource
    {
        Gate::authorize('create', DocumentFieldValue::class);
        $documentFieldValue = DocumentFieldValue::create($request->validated());
        
        // The observer has already run and potentially updated the document status
        $document = $documentFieldValue->documentField->documentSigner->document;
        $wasCompleted = $document->status === DocumentStatus::COMPLETED;
        
        return new DocumentFieldValueResource($documentFieldValue->load(['signatureSign']), [
            'document_completed' => $wasCompleted,
            'document_status' => $document->status
        ]);
    }
}