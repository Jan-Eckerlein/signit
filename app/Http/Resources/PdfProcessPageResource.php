<?php

namespace App\Http\Resources;

use App\Models\PdfProcessPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PdfProcessPage */
class PdfProcessPageResource extends JsonResource
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
            'pdf_process_id' => $this->pdf_process_id,
            'document_page_id' => $this->document_page_id,
            'pdf_original_path' => $this->pdf_original_path,
            'pdf_processed_path' => $this->pdf_processed_path,
            'is_up_to_date' => $this->is_up_to_date,
            'thumbnails' => PdfProcessPageThumbnailResource::collection($this->whenLoaded('thumbnails')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 