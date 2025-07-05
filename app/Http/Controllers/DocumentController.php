<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;
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
        $status = $request->is_template ? DocumentStatus::TEMPLATE : DocumentStatus::DRAFT;
        $document = Document::create($request->validated() + ['owner_user_id' => $request->user()->id, 'status' => $status]);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Create Document from Template
     */
    public function createFromTemplate(Request $request, Document $template): DocumentResource
    {
        Gate::authorize('create', Document::class);
        $document = Document::create([
            'title' => $template->title,
            'owner_user_id' => $request->user()->id,
            'description' => $template->description,
            'status' => DocumentStatus::DRAFT,
        ]);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    public function setInProgress(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        
        $document->status = DocumentStatus::IN_PROGRESS;
        $document->save();
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
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
} 