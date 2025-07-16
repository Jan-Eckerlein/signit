<?php

namespace App\Http\Controllers;

use App\Attributes\SharedPaginationParams;
use App\Http\Resources\DocumentPageResource;
use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;

class DocumentPageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return ResourceCollection<DocumentPageResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', DocumentPage::class);
        return DocumentPage::query()
            ->viewableBy($request->user())
            ->with('documentFields')
            ->paginateOrGetAll($request);
    }

    /**
     * Display the specified resource.
     * @return DocumentPageResource
     */
    public function show(DocumentPage $documentPage): DocumentPageResource
    {
        Gate::authorize('view', $documentPage);
        return new DocumentPageResource($documentPage->load('document', 'documentFields'));
    }
}
