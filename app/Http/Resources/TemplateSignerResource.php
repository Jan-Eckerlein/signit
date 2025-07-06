<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateSignerResource extends JsonResource
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
            'template_id' => $this->template_id,
            'name' => $this->name,
            'description' => $this->description,
            'template_fields' => TemplateFieldResource::collection($this->whenLoaded('templateFields')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 