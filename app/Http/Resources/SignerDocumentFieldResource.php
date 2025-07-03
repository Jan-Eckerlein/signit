<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignerDocumentFieldResource extends JsonResource
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
            'document_signer_id' => $this->document_signer_id,
            'page' => $this->page,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'label' => $this->label,
            'description' => $this->description,
            'required' => $this->required,
            'value_signature' => $this->value_signature,
            'value_initials' => $this->value_initials,
            'value_text' => $this->value_text,
            'value_checkbox' => $this->value_checkbox,
            'value_date' => $this->value_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document_signer' => new DocumentSignerResource($this->whenLoaded('documentSigner')),
        ];
    }
} 