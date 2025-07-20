<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Enums\Icon;
use App\Jobs\SendDocumentInProgressNotification;
use App\Jobs\SendMagicLinkNotification;
use App\Services\MagicLinkService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Attributes\SharedPaginationParams;
use App\Events\DocumentOpenedEvent;
use App\Http\Resources\DocumentProgressResource;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use App\Services\UserAgent;


const ALL_RELATIONS = [
    'ownerUser',
    'documentSigners',
    'documentLogs',
    'pdfProcess',
    'pdfProcess.pages',
    'pdfProcess.pages.thumbnails',
    'documentPages',
    'documentPages.documentFields',
];

/**
 * @group Documents
 */
class DocumentController extends Controller
{
    /**
     * List Documents
     * 
     * List all documents viewable by the user.
     */
    #[SharedPaginationParams]
    #[ResponseFromApiResource(DocumentResource::class, Document::class, collection: true, paginate: 20)]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', Document::class);
        return Document::viewableBy($request->user())
            ->with(ALL_RELATIONS)
            ->paginateOrGetAll($request);
    }

    /**
     * Create Document
     * 
     * Store a newly created document in storage.
     */
    #[ResponseFromApiResource(DocumentResource::class, Document::class)]
    public function store(StoreDocumentRequest $request): DocumentResource
    {
        $data = array_merge(
            $request->validated(),
            [
                'status' => $request->is_template ? DocumentStatus::TEMPLATE->value : DocumentStatus::DRAFT->value,
                'owner_user_id' => $request->user()->id,
            ]
        );
        $request->merge($data);
        Log::info('Creating document', ['request' => $request->all()]);
        Gate::authorize('create', Document::class);
        $document = Document::create($data);

        $document->pdfProcess()->create();

        return new DocumentResource($document->load(ALL_RELATIONS));
    }

    public function openForSigning(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        Log::info('setting document to in progress', ['document' => $document->id]);
        
        $document->status = DocumentStatus::OPEN;
        $document->save();

        $userAgent = new UserAgent($request);
        DocumentOpenedEvent::dispatch($document, $userAgent);
        
        return new DocumentResource($document->load(ALL_RELATIONS));
    }

    public function revertToDraft(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        $document->status = DocumentStatus::DRAFT;
        $document->save();
        return new DocumentResource($document->load(ALL_RELATIONS));
    }

    /**
     * Show Document
     * 
     * Display the specified document.
     */
    #[ResponseFromApiResource(DocumentResource::class, Document::class)]
    public function show(Request $request, Document $document): DocumentResource
    {
        Gate::authorize('view', $document);
        return new DocumentResource($document->load(ALL_RELATIONS));
    }

    /**
     * Update Document
     * 
     * Update the specified document in storage.
     */
    #[ResponseFromApiResource(DocumentResource::class, Document::class)]
    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        Gate::authorize('update', $document);
        $document->update($request->validated());
        return new DocumentResource($document->load(ALL_RELATIONS));
    }

    /**
     * Delete Document
     * 
     * Remove the specified document from storage.
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Get Document Progress
     * 
     * Retrieve the progress of the specified document.
     */
    #[ResponseFromApiResource(DocumentProgressResource::class, Document::class)]
    public function getProgress(Request $request, Document $document): DocumentProgressResource
    {
        Gate::authorize('view', $document);
        return new DocumentProgressResource($document);
    }
} 