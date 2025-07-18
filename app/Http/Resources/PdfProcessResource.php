<?php

namespace App\Http\Resources;

use App\Models\PdfProcess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PdfProcess */
class PdfProcessResource extends JsonResource
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
            'pdf_final_path' => $this->pdf_final_path,
            'pages' => PdfProcessPageResource::collection($this->whenLoaded('pages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
