<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignerDocumentFieldRequest;
use App\Http\Requests\UpdateSignerDocumentFieldRequest;
use App\Http\Resources\SignerDocumentFieldResource;
use App\Models\SignerDocumentField;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;

/**
 * @group Signer Document Fields
 */
class SignerDocumentFieldController extends Controller
{
    /**
     * List Signer Document Fields
     * 
     * List all signer document fields.
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\SignerDocumentFieldResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', SignerDocumentField::class);
        return SignerDocumentField::viewableBy($request->user())
            ->with(['documentSigner', 'value.signatureSign'])
            ->paginateOrGetAll($request);
    }

    /**
     * Create Signer Document Field
     * 
     * Store a newly created signer document field in storage.
     */
    public function store(StoreSignerDocumentFieldRequest $request): SignerDocumentFieldResource
    {
        Gate::authorize('create', SignerDocumentField::class);
        $signerDocumentField = SignerDocumentField::create($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Show Signer Document Field
     * 
     * Display the specified signer document field.
     */
    public function show(Request $request, SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        Gate::authorize('view', $signerDocumentField);
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Update Signer Document Field
     * 
     * Update the specified signer document field in storage.
     */
    public function update(UpdateSignerDocumentFieldRequest $request, SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        Gate::authorize('update', $signerDocumentField);
        $signerDocumentField->update($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Delete Signer Document Field
     * 
     * Remove the specified signer document field from storage.
     */
    public function destroy(Request $request, SignerDocumentField $signerDocumentField): JsonResponse
    {
        Gate::authorize('delete', $signerDocumentField);
        $signerDocumentField->delete();
        return response()->json(['message' => 'Signer document field deleted successfully']);
    }
} 