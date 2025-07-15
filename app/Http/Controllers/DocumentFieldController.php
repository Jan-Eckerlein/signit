<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentFieldRequest;
use App\Http\Requests\UpdateDocumentFieldRequest;
use App\Http\Resources\DocumentFieldResource;
use App\Models\DocumentField;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;

/**
 * @group Signer Document Fields
 */
class DocumentFieldController extends Controller
{
    /**
     * List Signer Document Fields
     * 
     * List all signer document fields.
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\DocumentFieldResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', DocumentField::class);
        return DocumentField::viewableBy($request->user())
            ->with(['documentSigner', 'value.signatureSign'])
            ->paginateOrGetAll($request);
    }

    /**
     * Create Signer Document Field
     * 
     * Store a newly created signer document field in storage.
     */
    public function store(StoreDocumentFieldRequest $request): DocumentFieldResource
    {
        Gate::authorize('create', DocumentField::class);
        $documentField = DocumentField::create($request->validated());
        return new DocumentFieldResource($documentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Show Signer Document Field
     * 
     * Display the specified signer document field.
     */
    public function show(Request $request, DocumentField $documentField): DocumentFieldResource
    {
        Gate::authorize('view', $documentField);
        return new DocumentFieldResource($documentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Update Signer Document Field
     * 
     * Update the specified signer document field in storage.
     */
    public function update(UpdateDocumentFieldRequest $request, DocumentField $documentField): DocumentFieldResource
    {
        Gate::authorize('update', $documentField);
        $documentField->update($request->validated());
        return new DocumentFieldResource($documentField->load(['documentSigner', 'value.signatureSign']));
    }

    /**
     * Delete Signer Document Field
     * 
     * Remove the specified signer document field from storage.
     */
    public function destroy(Request $request, DocumentField $documentField): JsonResponse
    {
        Gate::authorize('delete', $documentField);
        $documentField->delete();
        return response()->json(['message' => 'Signer document field deleted successfully']);
    }
} 