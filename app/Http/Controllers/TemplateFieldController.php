<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateFieldRequest;
use App\Http\Requests\UpdateTemplateFieldRequest;
use App\Http\Resources\TemplateFieldResource;
use App\Models\Template;
use App\Models\TemplateField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TemplateFieldController extends Controller
{
    public function index(Template $template): AnonymousResourceCollection
    {
        Gate::authorize('view', $template);
        
        $templateFields = $template->templateFields()->get();

        return TemplateFieldResource::collection($templateFields);
    }

    public function store(StoreTemplateFieldRequest $request, Template $template): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateField = $template->templateFields()->create($request->validated());

        return response()->json([
            'message' => 'Template field created successfully',
            'template_field' => new TemplateFieldResource($templateField)
        ], 201);
    }

    public function show(Template $template, TemplateField $templateField): TemplateFieldResource
    {
        Gate::authorize('view', $template);
        
        return new TemplateFieldResource($templateField);
    }

    public function update(UpdateTemplateFieldRequest $request, Template $template, TemplateField $templateField): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateField->update($request->validated());

        return response()->json([
            'message' => 'Template field updated successfully',
            'template_field' => new TemplateFieldResource($templateField)
        ]);
    }

    public function destroy(Template $template, TemplateField $templateField): JsonResponse
    {
        Gate::authorize('update', $template);
        
        $templateField->delete();

        return response()->json([
            'message' => 'Template field deleted successfully'
        ]);
    }
} 