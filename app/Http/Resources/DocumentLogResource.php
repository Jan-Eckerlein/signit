<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'contact_id' => $this->contact_id,
            'document_id' => $this->document_id,
            'ip' => $this->ip,
            'date' => $this->date,
            'icon' => $this->icon,
            'text' => $this->text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'document' => new DocumentResource($this->whenLoaded('document')),
        ];
    }
} 