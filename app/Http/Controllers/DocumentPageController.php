<?php

namespace App\Http\Controllers;

use App\Attributes\SharedPaginationParams;
use App\Http\Resources\DocumentPageResource;
use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[SharedPaginationParams]
    public function index(Request $request)
    {
        Gate::authorize('viewAny', DocumentPage::class);
        return DocumentPage::viewableBy($request->user())
            ->with('documentFields')
            ->paginateOrGetAll($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentPage $documentPage)
    {
        Gate::authorize('view', $documentPage);
        return new DocumentPageResource($documentPage->load('document', 'documentFields'));
    }
}
