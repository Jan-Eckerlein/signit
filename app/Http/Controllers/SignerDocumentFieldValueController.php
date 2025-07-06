<?php

namespace App\Http\Controllers;

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
        return new SignerDocumentFieldValueResource($signerDocumentFieldValue->load(['signatureSign']));
    }
}
