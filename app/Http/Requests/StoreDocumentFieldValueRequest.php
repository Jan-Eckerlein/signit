<?php

namespace App\Http\Requests;

use App\Services\DocumentFieldValueValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDocumentFieldValueRequest extends FormRequest
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
            'signer_document_field_id' => 'required|exists:signer_document_fields,id',
            'value_signature_sign_id' => 'nullable|exists:signs,id',
            'value_initials' => 'nullable|string|max:255',
            'value_text' => 'nullable|string',
            'value_checkbox' => 'nullable|boolean',
            'value_date' => 'nullable|date',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateValueFields($validator);
        });
    }
    
    /**
     * Validate value fields using the shared validation service.
     */
    private function validateValueFields(Validator $validator): void
    {
        $documentFieldId = $this->input('signer_document_field_id');
        
        if (!$documentFieldId) {
            return; // Let the exists rule handle this
        }
        
        $field = \App\Models\DocumentField::find($documentFieldId);
        
        if (!$field) {
            return; // Let the exists rule handle this
        }
        
        DocumentFieldValueValidationService::addValidationErrors(
            $validator, 
            $this->all(), 
            $field->type
        );
    }
}
