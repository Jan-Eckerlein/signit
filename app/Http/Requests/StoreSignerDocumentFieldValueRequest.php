<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignerDocumentFieldValueRequest extends FormRequest
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
}
