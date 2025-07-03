<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignerDocumentFieldRequest;
use App\Http\Requests\UpdateSignerDocumentFieldRequest;
use App\Http\Resources\SignerDocumentFieldResource;
use App\Models\SignerDocumentField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SignerDocumentFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $signerDocumentFields = SignerDocumentField::with(['documentSigner', 'signatureSign'])->paginate();
        return SignerDocumentFieldResource::collection($signerDocumentFields);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSignerDocumentFieldRequest $request): SignerDocumentFieldResource
    {
        $signerDocumentField = SignerDocumentField::create($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'signatureSign']));
    }

    /**
     * Display the specified resource.
     */
    public function show(SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'signatureSign']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSignerDocumentFieldRequest $request, SignerDocumentField $signerDocumentField): SignerDocumentFieldResource
    {
        $signerDocumentField->update($request->validated());
        return new SignerDocumentFieldResource($signerDocumentField->load(['documentSigner', 'signatureSign']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SignerDocumentField $signerDocumentField): JsonResponse
    {
        $signerDocumentField->delete();
        return response()->json(['message' => 'Signer document field deleted successfully']);
    }
} 