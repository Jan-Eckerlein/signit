<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentSignerRequest;
use App\Http\Requests\UpdateDocumentSignerRequest;
use App\Http\Resources\DocumentSignerResource;
use App\Models\DocumentSigner;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentSignerController extends Controller
{
    /**
     * @group Document Signers
     * @title "List Document Signers"
     * @description "List all document signers"
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\DocumentSignerResource>
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', DocumentSigner::class);
        $documentSigners = DocumentSigner::viewableBy($request->user())
            ->with(['document', 'user', 'signerDocumentFields'])
            ->paginate();
        return DocumentSignerResource::collection($documentSigners);
    }

    /**
     * @group Document Signers
     * @title "Create Document Signer"
     * @description "Create a new document signer"
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentSignerRequest $request): DocumentSignerResource
    {
        Gate::authorize('create', DocumentSigner::class);
        $documentSigner = DocumentSigner::create($request->validated());
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * @group Document Signers
     * @title "Show Document Signer"
     * @description "Show a document signer"
     * Display the specified resource.
     */
    public function show(Request $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        Gate::authorize('view', $documentSigner);
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * @group Document Signers
     * @title "Update Document Signer"
     * @description "Update a document signer"
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentSignerRequest $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        Gate::authorize('update', $documentSigner);
        $documentSigner->update($request->validated());
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * @group Document Signers
     * @title "Delete Document Signer"
     * @description "Delete a document signer"
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DocumentSigner $documentSigner): JsonResponse
    {
        Gate::authorize('delete', $documentSigner);
        $documentSigner->delete();
        return response()->json(['message' => 'Document signer deleted successfully']);
    }
} 