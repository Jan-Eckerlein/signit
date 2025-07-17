<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentLogResource;
use App\Models\DocumentLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
/**
 * @group Document Logs
 */
class DocumentLogsController extends Controller
{
    /**
     * List Document Logs
     * 
     * List all document logs that the user can view.
     */
    #[ResponseFromApiResource(DocumentLogResource::class, DocumentLog::class, collection: true, paginate: 20)]
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', DocumentLog::class);
        
        return DocumentLog::viewableBy($request->user())
            ->with(['document', 'documentSigner.user'])
            ->orderBy('date', 'desc')
            ->paginateOrGetAll($request);
    }



    /**
     * Show Document Log
     * 
     * Display the specified document log.
     */
    #[ResponseFromApiResource(DocumentLogResource::class, DocumentLog::class)]
    public function show(Request $request, DocumentLog $documentLog): DocumentLogResource
    {
        Gate::authorize('view', $documentLog);
        
        $documentLog->load(['document', 'documentSigner.user']);
        
        return new DocumentLogResource($documentLog);
    }
} 