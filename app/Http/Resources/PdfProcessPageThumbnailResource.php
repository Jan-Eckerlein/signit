<?php

namespace App\Http\Resources;

use App\Models\PdfProcessPageThumbnail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PdfProcessPageThumbnail */
class PdfProcessPageThumbnailResource extends JsonResource
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
            'pdf_process_page_id' => $this->pdf_process_page_id,
            'path' => $this->path,
            'size' => $this->size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 