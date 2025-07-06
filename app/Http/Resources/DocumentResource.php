<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'title' => $this->title,
            'owner_user_id' => $this->owner_user_id,
            'description' => $this->description,
            'status' => $this->status,
            'template_id' => $this->template_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'owner_user' => new UserResource($this->whenLoaded('ownerUser')),
            'document_signers' => DocumentSignerResource::collection($this->whenLoaded('documentSigners')),
            'document_logs' => DocumentLogResource::collection($this->whenLoaded('documentLogs')),
        ];
    }
} 