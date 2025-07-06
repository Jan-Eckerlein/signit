<?php

namespace App\Http\Requests;

use App\Enums\DocumentFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSignerDocumentFieldRequest extends FormRequest
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
            'document_id' => 'required|exists:documents,id',
            'document_signer_id' => 'nullable|exists:document_signers,id',
            'page' => 'required|integer|min:1',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'type' => ['required', new Enum(DocumentFieldType::class)],
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required' => 'boolean',
        ];
    }
} 