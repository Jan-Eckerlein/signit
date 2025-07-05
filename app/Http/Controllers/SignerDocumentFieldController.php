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

class SignerDocumentFieldController extends Controller
{
    /**
     * @group Signer Document Fields
     * @title "List Signer Document Fields"
     * @description "List all signer document fields"
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
     * @group Signer Document Fields
     * @title "Create Signer Document Field"
     * @description "Create a new signer document field"
     * Store a newly created resource in storage.
     */
    public function store(StoreSignerDocumentFieldRequest $request): SignerDocumentFieldResource
    {
        Gate::authorize('create', SignerDocumentField::class);
        $signerDocumentField = SignerDocumentField::create($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * @group Signer Document Fields
     * @title "Show Signer Document Field"
     * @description "Show a signer document field"
     * Display the specified resource.
     */
    public function show(Request $request, SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        Gate::authorize('view', $signerDocumentField);
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * @group Signer Document Fields
     * @title "Update Signer Document Field"
     * @description "Update a signer document field"
     * Update the specified resource in storage.
     */
    public function update(UpdateSignerDocumentFieldRequest $request, SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        Gate::authorize('update', $signerDocumentField);
        $signerDocumentField->update($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * @group Signer Document Fields
     * @title "Delete Signer Document Field"
     * @description "Delete a signer document field"
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, SignerDocumentField $signerDocumentField): JsonResponse
    {
        Gate::authorize('delete', $signerDocumentField);
        $signerDocumentField->delete();
        return response()->json(['message' => 'Signer document field deleted successfully']);
    }
} 