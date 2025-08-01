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
use App\Events\SignatureCompletedEvent;
use App\Models\User;
use App\Services\UserAgent;

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
            ->with(['document', 'user', 'documentFields'])
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
        $validated = $request->validated();
        if ($validated['email']) {
            $user = User::firstOrCreate(['email' => $validated['email']]);
            $validated['user_id'] = $user->id;
        }
        $documentSigner = DocumentSigner::create($validated);
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'documentFields']));
    }

    /**
     * Show Document Signer
     * 
     * Display the specified document signer.
     */
    public function show(Request $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        Gate::authorize('view', $documentSigner);
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'documentFields']));
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
        return new DocumentSignerResource($documentSigner->load(['document', 'user', 'documentFields']));
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
        Gate::authorize('completeSignature', $documentSigner);
        
        $request->validate([
            'electronic_signature_disclosure_accepted' => 'required|boolean|accepted',
        ]);
        
        $documentSigner->completeSignature();

        SignatureCompletedEvent::dispatch($documentSigner, UserAgent::fromRequest($request));
        
        // Observer will handle all notifications and status updates
        return response()->json([
            'message' => 'Signature completed successfully',
            'document_status' => $documentSigner->document->status,
            'is_document_completed' => $documentSigner->document->status === \App\Enums\DocumentStatus::COMPLETED,
        ]);
    }
} 