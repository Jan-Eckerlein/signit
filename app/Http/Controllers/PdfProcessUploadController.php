<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePdfProcessUploadRequest;
use App\Models\PdfProcess;
use App\Services\PdfProcessUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;

#[Group('Pdf Process Uploads', 'API for Uploading new raw pdfs to a pdf process')]
class PdfProcessUploadController extends Controller
{

    public function __construct(
        private PdfProcessUploadService $pdfProcessUploadService
    ) {}

    /**
     * Upload pdf(s)
     * 
     * Upload new raw pdfs to a pdf process.
     */
    public function store(StorePdfProcessUploadRequest $request, PdfProcess $pdfProcess): JsonResponse
    {
        Gate::authorize('update', $pdfProcess);
        
        $files = $request->file('pdfs');
        $orders = $request->input('orders');
        foreach ($files as $i => $file) {
            $order = $orders[$i] ?? null;
            $this->pdfProcessUploadService->upload($pdfProcess, $file, $order);
        }

        return response()->json([
            'message' => 'Pdf process upload created successfully',
        ]);
    }
}
