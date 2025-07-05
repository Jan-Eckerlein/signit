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


class DocumentLogsController extends Controller
{
    /**
     * @group Document Logs
     * @title "List Document Logs"
     * @description "List all document logs that the user can view"
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\DocumentLogResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', DocumentLog::class);
        
        $query = DocumentLog::viewableBy($request->user())
            ->with(['document', 'documentSigner.user'])
            ->orderBy('date', 'desc');
        
        return DocumentLog::paginateOrGetAll($query, $request);
    }



    /**
     * @group Document Logs
     * @title "Show Document Log"
     * @description "Show a specific document log"
     * @return \App\Http\Resources\DocumentLogResource
     */
    public function show(Request $request, DocumentLog $documentLog): DocumentLogResource
    {
        Gate::authorize('view', $documentLog);
        
        $documentLog->load(['document', 'documentSigner.user']);
        
        return new DocumentLogResource($documentLog);
    }
} 