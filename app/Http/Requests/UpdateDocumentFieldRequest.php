<?php

namespace App\Http\Requests;

use App\Enums\DocumentFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDocumentFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_id' => 'sometimes|exists:documents,id',
            'document_signer_id' => 'sometimes|exists:document_signers,id',
            'page' => 'sometimes|integer|min:1',
            'x' => 'sometimes|numeric',
            'y' => 'sometimes|numeric',
            'width' => 'sometimes|numeric|min:0',
            'height' => 'sometimes|numeric|min:0',
            'type' => ['sometimes', new Enum(DocumentFieldType::class)],
            'label' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'required' => 'sometimes|boolean',
        ];
    }
} 