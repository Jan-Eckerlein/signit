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

/**
 * @group Document Signers
 */
class DocumentSignerController extends Controller
{
    /**
     * List Document Signers
     * 
     * List all document signers.
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
     * Create Document Signer
     * 
     * Store a newly created document signer in storage.
     */
    public function store(StoreDocumentSignerRequest $request): DocumentSignerResource
    {
        Gate::authorize('create', DocumentSigner::class);
        if ($request->email) {
            $user = User::firstOrCreate(['email' => $request->email]);
            $request->merge(['user_id' => $user->id]);
        }
        $documentSigner = DocumentSigner::create($request->validated() + ['user_id' => $user->id]);
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * Show Document Signer
     * 
     * Display the specified document signer.
     */
    public function show(Request $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        Gate::authorize('view', $documentSigner);
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * Update Document Signer
     * 
     * Update the specified document signer in storage.
     */
    public function update(UpdateDocumentSignerRequest $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        Gate::authorize('update', $documentSigner);
        if ($request->email) {
            $user = User::firstOrCreate(['email' => $request->email]);
            $request->merge(['user_id' => $user->id]);
        }
        $documentSigner->update($request->validated());
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'signerDocumentFields']));
    }

    /**
     * Delete Document Signer
     * 
     * Remove the specified document signer from storage.
     */
    public function destroy(Request $request, DocumentSigner $documentSigner): JsonResponse
    {
        Gate::authorize('delete', $documentSigner);
        $documentSigner->delete();
        return response()->json(['message' => 'Document signer deleted successfully']);
    }

    /**
     * Complete Signature
     * 
     * Complete the signature process and accept electronic disclosure.
     */
    public function completeSignature(Request $request, DocumentSigner $documentSigner): JsonResponse
    {
        Gate::authorize('update', $documentSigner);
        
        $request->validate([
            'electronic_signature_disclosure_accepted' => 'required|boolean|accepted',
        ]);
        
        // Update signer completion status
        $documentSigner->update([
            'signature_completed_at' => now(),
            'electronic_signature_disclosure_accepted' => true,
            'disclosure_accepted_at' => now(),
        ]);
        
        // Observer will handle all notifications and status updates
        return response()->json([
            'message' => 'Signature completed successfully',
            'document_status' => $documentSigner->document->status,
            'is_document_completed' => $documentSigner->document->status === \App\Enums\DocumentStatus::COMPLETED,
        ]);
    }
} 