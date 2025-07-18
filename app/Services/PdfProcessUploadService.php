<?php

namespace App\Services;

use App\Models\PdfProcess;
use Illuminate\Http\UploadedFile;

class PdfProcessUploadService
{
    public function upload(PdfProcess $pdfProcess, UploadedFile $file, int $order): void
    {
        $pdfProcess->uploads()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $file->store('uploads', 'local'),
            'size' => $file->getSize(),
            'order' => $order,
        ]);
    }
}
