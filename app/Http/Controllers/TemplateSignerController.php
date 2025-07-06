<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateSignerRequest;
use App\Http\Requests\UpdateTemplateSignerRequest;
use App\Http\Resources\TemplateSignerResource;
use App\Models\Template;
use App\Models\TemplateSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TemplateSignerController extends Controller
{
    public function index(Template $template): AnonymousResourceCollection
    {
        Gate::authorize('view', $template);
        
        $templateSigners = $template->templateSigners()
            ->with(['templateFields'])
            ->get();

        return TemplateSignerResource::collection($templateSigners);
    }

    public function store(StoreTemplateSignerRequest $request, Template $template): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateSigner = $template->templateSigners()->create($request->validated());

        return response()->json([
            'message' => 'Template signer created successfully',
            'template_signer' => new TemplateSignerResource($templateSigner)
        ], 201);
    }

    public function show(Template $template, TemplateSigner $templateSigner): TemplateSignerResource
    {
        Gate::authorize('view', $template);
        
        $templateSigner->load(['templateFields']);
        
        return new TemplateSignerResource($templateSigner);
    }

    public function update(UpdateTemplateSignerRequest $request, Template $template, TemplateSigner $templateSigner): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateSigner->update($request->validated());

        return response()->json([
            'message' => 'Template signer updated successfully',
            'template_signer' => new TemplateSignerResource($templateSigner)
        ]);
    }

    public function destroy(Template $template, TemplateSigner $templateSigner): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateSigner->delete();

        return response()->json([
            'message' => 'Template signer deleted successfully'
        ]);
    }
} 