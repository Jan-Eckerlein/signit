<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'description' => $this->description,
            'owner_user_id' => $this->owner_user_id,
            'template_signers' => TemplateSignerResource::collection($this->whenLoaded('templateSigners')),
            'template_fields' => TemplateFieldResource::collection($this->whenLoaded('templateFields')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 