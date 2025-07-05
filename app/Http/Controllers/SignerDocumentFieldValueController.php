<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignerDocumentFieldValueRequest;
use App\Http\Requests\UpdateSignerDocumentFieldValueRequest;
use App\Http\Resources\SignerDocumentFieldValueResource;
use App\Models\SignerDocumentFieldValue;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SignerDocumentFieldValueController extends Controller
{
    /**
     * @group Signer Document Field Values
     * @title "List Signer Document Field Values"
     * @description "List all signer document field values"
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\SignerDocumentFieldValueResource>
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SignerDocumentFieldValue::class);
        $signerDocumentFieldValues = SignerDocumentFieldValue::viewableBy($request->user())
            ->with(['signatureSign'])
            ->paginate();
        return SignerDocumentFieldValueResource::collection($signerDocumentFieldValues);
    }

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

    /**
     * @group Signer Document Field Values
     * @title "Show Signer Document Field Value"
     * @description "Show a signer document field value"
     * Display the specified resource.
     */
    public function show(Request $request, SignerDocumentFieldValue $signerDocumentFieldValue): SignerDocumentFieldValueResource
    {
        Gate::authorize('view', $signerDocumentFieldValue);
        return new SignerDocumentFieldValueResource($signerDocumentFieldValue->load(['signatureSign']));
    }

    /**
     * @group Signer Document Field Values
     * @title "Update Signer Document Field Value"
     * @description "Update a signer document field value"
     * Update the specified resource in storage.
     */
    public function update(UpdateSignerDocumentFieldValueRequest $request, SignerDocumentFieldValue $signerDocumentFieldValue): SignerDocumentFieldValueResource
    {
        Gate::authorize('update', $signerDocumentFieldValue);
        $signerDocumentFieldValue->update($request->validated());
        return new SignerDocumentFieldValueResource($signerDocumentFieldValue->load(['signatureSign']));
    }

    /**
     * @group Signer Document Field Values
     * @title "Delete Signer Document Field Value"
     * @description "Delete a signer document field value"
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, SignerDocumentFieldValue $signerDocumentFieldValue): JsonResponse
    {
        Gate::authorize('delete', $signerDocumentFieldValue);
        $signerDocumentFieldValue->delete();
        return response()->json(['message' => 'Signer document field value deleted successfully']);
    }
}
