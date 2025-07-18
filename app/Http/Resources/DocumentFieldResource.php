<?php

namespace App\Http\Resources;

use App\Models\DocumentField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DocumentField */
class DocumentFieldResource extends JsonResource
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
            'document_page_id' => $this->document_page_id,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'label' => $this->label,
            'description' => $this->description,
            'required' => $this->required,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document_signer' => new DocumentSignerResource($this->whenLoaded('documentSigner')),
            'document_page' => new DocumentPageResource($this->whenLoaded('documentPage')),
            'value' => new DocumentFieldValueResource($this->whenLoaded('value')),
        ];
    }
} 