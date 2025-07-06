<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateFieldRequest extends FormRequest
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
            'template_signer_id' => 'sometimes|nullable|exists:template_signers,id',
            'field_type' => 'sometimes|required|string|max:255',
            'x' => 'sometimes|required|numeric',
            'y' => 'sometimes|required|numeric',
            'width' => 'sometimes|required|numeric',
            'height' => 'sometimes|required|numeric',
            'page' => 'sometimes|required|integer|min:1',
        ];
    }
} 