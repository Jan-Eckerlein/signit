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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class DocumentController extends Controller
{
    /**
     * @group Documents
     * @title "List Documents"
     * @description "List all documents"
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\DocumentResource>
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Document::class);
        $documents = Document::viewableBy($request->user())
            ->with(['ownerUser', 'documentSigners', 'documentLogs'])
            ->paginate();
        return DocumentResource::collection($documents);
    }

    /**
     * @group Documents
     * @title "Create Document"
     * @description "Create a new document"
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): DocumentResource
    {
        Gate::authorize('create', Document::class);
        $status = $request->is_template ? DocumentStatus::TEMPLATE : DocumentStatus::DRAFT;
        $document = Document::create($request->validated() + ['owner_user_id' => $request->user()->id, 'status' => $status]);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * @group Documents
     * @title "Show Document"
     * @description "Show a document"
     * Display the specified resource.
     */
    public function show(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('view', $document);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * @group Documents
     * @title "Update Document"
     * @description "Update a document"
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        $document->update($request->validated());
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * @group Documents
     * @title "Delete Document"
     * @description "Delete a document"
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
} 