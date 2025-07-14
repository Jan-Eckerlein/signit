<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group Document Signers
 * 
 * Document Signer Resource
 * 
 * This resource represents a document signer, which links a user to a document
 * for signing purposes.
 */
class DocumentSignerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'document_id' => $this->document_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document' => new DocumentResource($this->whenLoaded('document')),
            'user' => new UserResource($this->whenLoaded('user')),
            'signer_document_fields' => SignerDocumentFieldResource::collection($this->whenLoaded('signerDocumentFields')),
        ];
    }
} 