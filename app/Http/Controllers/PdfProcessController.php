<?php

namespace App\Http\Controllers;

use App\Attributes\SharedPaginationParams;
use App\Http\Resources\PdfProcessResource;
use App\Models\PdfProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
class PdfProcessController extends Controller
{
    /**
     * List Pdf Processes
     * 
     * List all pdf processes that are owned by the current user.
     */
    #[SharedPaginationParams]
    #[ResponseFromApiResource(PdfProcessResource::class, PdfProcess::class, collection: true, paginate: 20)]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', PdfProcess::class);
        return PdfProcess::viewableBy()
            ->paginateOrGetAll($request);
    }

    /**
     * Show Pdf Process
     * 
     * Show a pdf process.
     */
    #[ResponseFromApiResource(PdfProcessResource::class, PdfProcess::class)]
    public function show(PdfProcess $pdfProcess): PdfProcessResource
    {
        Gate::authorize('view', $pdfProcess);
        return new PdfProcessResource($pdfProcess);
    }
}
