<?php

namespace App\Http\Controllers;

use App\Attributes\SharedPaginationParams;
use App\Http\Resources\PdfProcessResource;
use App\Models\PdfProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PdfProcessController extends Controller
{
    /**
     * List Pdf Processes
     * 
     * List all pdf processes that are owned by the current user.
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\PdfProcessResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request)
    {
        Gate::authorize('viewAny', PdfProcess::class);
        return PdfProcess::viewableBy()
            ->paginateOrGetAll($request);
    }

    /**
     * Show Pdf Process
     * 
     * Show a pdf process.
     * @param \App\Models\PdfProcess $pdfProcess
     * @return \App\Http\Resources\PdfProcessResource
     */
    public function show(PdfProcess $pdfProcess)
    {
        Gate::authorize('view', $pdfProcess);
        return new PdfProcessResource($pdfProcess);
    }
}
