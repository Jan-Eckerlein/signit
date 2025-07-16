<?php

namespace App\Http\Resources;

use App\Models\DocumentLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DocumentLog */
class DocumentLogResource extends JsonResource
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
            'document_id' => $this->document_id,
            'ip' => $this->ip,
            'date' => $this->date,
            'icon' => $this->icon,
            'text' => $this->text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document_signer' => new DocumentSignerResource($this->whenLoaded('documentSigner')),
            'document' => new DocumentResource($this->whenLoaded('document')),
        ];
    }
} 