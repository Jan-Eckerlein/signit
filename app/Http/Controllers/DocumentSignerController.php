<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentSignerRequest;
use App\Http\Requests\UpdateDocumentSignerRequest;
use App\Http\Resources\DocumentSignerResource;
use App\Models\DocumentSigner;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;
use App\Models\User;

class DocumentSignerController extends Controller
{
    /**
     * @group Document Signers
     * @title "List Document Signers"
     * @description "List all document signers"
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\DocumentSignerResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', DocumentSigner::class);
        return DocumentSigner::viewableBy()
            ->with(['document', 'user', 'signerDocumentFields'])
            ->paginateOrGetAll($request);
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
        $user = User::firstOrCreate(['email' => $request->email]);
        $documentSigner = DocumentSigner::create($request->validated() + ['user_id' => $user->id]);
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