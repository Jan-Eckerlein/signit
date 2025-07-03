<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentSignerRequest;
use App\Http\Requests\UpdateDocumentSignerRequest;
use App\Http\Resources\DocumentSignerResource;
use App\Models\DocumentSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentSignerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $documentSigners = DocumentSigner::with(['document', 'contact', 'signerDocumentFields'])->paginate();
        return DocumentSignerResource::collection($documentSigners);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentSignerRequest $request): DocumentSignerResource
    {
        $documentSigner = DocumentSigner::create($request->validated());
        return new DocumentSignerResource($documentSigner->load(['document', 'contact', 'signerDocumentFields']));
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentSigner $documentSigner): DocumentSignerResource
    {
        return new DocumentSignerResource($documentSigner->load(['document', 'contact', 'signerDocumentFields']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentSignerRequest $request, DocumentSigner $documentSigner): DocumentSignerResource
    {
        $documentSigner->update($request->validated());
        return new DocumentSignerResource($documentSigner->load(['document', 'contact', 'signerDocumentFields']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentSigner $documentSigner): JsonResponse
    {
        $documentSigner->delete();
        return response()->json(['message' => 'Document signer deleted successfully']);
    }
} 