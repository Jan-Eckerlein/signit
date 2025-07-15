<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentPageResource extends JsonResource
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
            'page_number' => $this->page_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'signer_document_fields' => DocumentFieldResource::collection($this->whenLoaded('documentFields')),
        ];
    }
}
