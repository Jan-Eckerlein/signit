<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Models\Template;
use App\Models\Document;
use App\Models\DocumentSigner;
use App\Models\SignerDocumentField;
use App\Models\Contact;
use App\Enums\DocumentStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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

    public function copyToDocument(Template $template, Request $request): JsonResponse
    {
        Gate::authorize('view', $template);
		Gate::authorize('create', Document::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'signer_mappings' => 'nullable|array',
            'signer_mappings.*.template_signer_id' => 'required|exists:template_signers,id',
            'signer_mappings.*.email' => 'required|email',
        ]);

        // Create the new document
        $document = Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_user_id' => Auth::id(),
            'status' => DocumentStatus::DRAFT,
            'template_id' => $template->id,
        ]);

        // Copy template signers to document signers
        $signerMappings = $request->signer_mappings ? collect($request->signer_mappings)->keyBy('template_signer_id') : collect();
        
        foreach ($template->templateSigners as $templateSigner) {
            // Determine if this signer should be bound to a user
            $user = null;
            if ($signerMappings->has($templateSigner->id)) {
                $mapping = $signerMappings->get($templateSigner->id);
                $user = User::firstOrCreate(['email' => $mapping['email']]);
            }
            
            $documentSigner = DocumentSigner::create([
                'document_id' => $document->id,
                'user_id' => $user?->id, // Will be null if no mapping
                'name' => $templateSigner->name,
            ]);

            // Copy fields for this signer
            foreach ($templateSigner->templateFields as $templateField) {
                SignerDocumentField::create([
                    'document_id' => $document->id,
                    'document_signer_id' => $documentSigner->id,
                    'page' => $templateField->page,
                    'x' => $templateField->x,
                    'y' => $templateField->y,
                    'width' => $templateField->width,
                    'height' => $templateField->height,
                    'type' => $templateField->field_type,
                    'label' => $templateField->label ?? '',
                    'description' => $templateField->description,
                    'required' => $templateField->required ?? false,
                ]);
            }
        }

        // Copy unbound template fields
        foreach ($template->templateFields()->whereNull('template_signer_id')->get() as $templateField) {
            SignerDocumentField::create([
                'document_id' => $document->id,
                'document_signer_id' => null, // Unbound field
                'page' => $templateField->page,
                'x' => $templateField->x,
                'y' => $templateField->y,
                'width' => $templateField->width,
                'height' => $templateField->height,
                'type' => $templateField->field_type,
                'label' => $templateField->label ?? '',
                'description' => $templateField->description,
                'required' => $templateField->required ?? false,
            ]);
        }

        return response()->json([
            'message' => 'Template copied to document successfully',
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'status' => $document->status,
            ]
        ], 201);
    }
} 