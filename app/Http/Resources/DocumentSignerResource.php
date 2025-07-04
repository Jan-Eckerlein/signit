<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group Document Signers
 * 
 * Document Signer Resource
 * 
 * This resource represents a document signer, which links a contact to a document
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
            'document_id' => $this->document_id,
            'contact_id' => $this->contact_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document' => new DocumentResource($this->whenLoaded('document')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'signer_document_fields' => SignerDocumentFieldResource::collection($this->whenLoaded('signerDocumentFields')),
        ];
    }
} 