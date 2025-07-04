<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentController extends Controller
{
    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\DocumentResource>
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $documents = Document::viewableBy($request->user())
            ->with(['ownerUser', 'documentSigners', 'documentLogs'])
            ->paginate();
        return DocumentResource::collection($documents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): DocumentResource
    {
        $status = $request->is_template ? DocumentStatus::TEMPLATE : DocumentStatus::DRAFT;
        $document = Document::create($request->validated() + ['owner_user_id' => $request->user()->id, 'status' => $status]);
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document): DocumentResource
    {
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        $document->update($request->validated());
        return new DocumentResource($document->load(['ownerUser', 'documentSigners', 'documentLogs']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document): JsonResponse
    {
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
} 