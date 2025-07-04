<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $documents = Document::with(['ownerUser', 'documentSigners', 'documentLogs'])->paginate();
        return DocumentResource::collection($documents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): DocumentResource
    {
        $document = Document::create($request->validated() + ['owner_user_id' => $request->user()->id]);
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