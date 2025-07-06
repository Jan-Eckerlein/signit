<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
		Gate::authorize('viewAny', Template::class);
        $templates = Template::ownedBy()
            ->with(['templateSigners.templateFields', 'templateFields'])
            ->get();

        return TemplateResource::collection($templates);
    }

    public function store(StoreTemplateRequest $request): JsonResponse
    {
		Gate::authorize('create', Template::class);
        $template = Template::create($request->validated());

        return response()->json([
            'message' => 'Template created successfully',
            'template' => new TemplateResource($template)
        ], 201);
    }

    public function show(Template $template): TemplateResource
    {
        Gate::authorize('view', $template);
        
        $template->load(['templateSigners.templateFields', 'templateFields']);
        
        return new TemplateResource($template);
    }

    public function update(UpdateTemplateRequest $request, Template $template): JsonResponse
    {
		Gate::authorize('update', $template);
        $template->update($request->validated());

        return response()->json([
            'message' => 'Template updated successfully',
            'template' => new TemplateResource($template)
        ]);
    }

    public function destroy(Template $template): JsonResponse
    {
        Gate::authorize('delete', $template);
        
        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully'
        ]);
    }
} 