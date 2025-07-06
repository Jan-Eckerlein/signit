<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Enums\Icon;
use App\Jobs\SendDocumentInProgressNotification;
use App\Jobs\SendMagicLinkNotification;
use App\Services\MagicLinkService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;

/**
 * @group Documents
 */
class DocumentController extends Controller
{
    /**
     * List Documents
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\DocumentResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', Document::class);
        return Document::viewableBy($request->user())
            ->with(['ownerUser', 'documentSigners', 'documentLogs'])
            ->paginateOrGetAll($request);
    }

    /**
     * Create Document
     */
    public function store(StoreDocumentRequest $request): DocumentResource
    {
        Gate::authorize('create', Document::class);
        $document = Document::create($request->validated());
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    public function setInProgress(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        
        try {
            // Update document status
            $document->status = DocumentStatus::IN_PROGRESS;
            $document->save();
            
            // Create audit log
            DocumentLog::create([
                'document_id' => $document->id,
                'document_signer_id' => null, // System action
                'ip' => $request->ip(),
                'date' => now(),
                'icon' => Icon::SEND,
                'text' => "Document set to in progress by {$request->user()->name}",
            ]);
            
            // Handle notifications for all document signers
            $document->load('documentSigners.user');
            $magicLinkService = new MagicLinkService();
            
            foreach ($document->documentSigners as $documentSigner) {
                if (!$documentSigner->user) {
                    continue; // Skip if no user associated
                }
                
                if ($documentSigner->user->isAnonymous()) {
                    // For anonymous users, create magic link and send notification
                    $token = $magicLinkService->createMagicLink($documentSigner->user, $document);
                    SendMagicLinkNotification::dispatch($document, $documentSigner->user, $token);
                } else {
                    // For regular users, send standard email notification
                    SendDocumentInProgressNotification::dispatch($document, $documentSigner->user);
                }
            }
            
            Log::info('Document set to in progress', [
                'document_id' => $document->id,
                'user_id' => $request->user()->id,
                'signers_notified' => $document->documentSigners->count(),
            ]);
            
            return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
            
        } catch (\Exception $e) {
            Log::error('Failed to set document to in progress', [
                'document_id' => $document->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Show Document
     */
    public function show(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('view', $document);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Update Document
     */
    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        $document->update($request->validated());
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Delete Document
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Get Document Progress
     */
    public function getProgress(Request $request, Document $document): JsonResponse
    {
        Gate::authorize('view', $document);
        
        return response()->json($document->getProgress());
    }
} 