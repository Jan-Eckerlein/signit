<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePdfProcessUploadRequest extends FormRequest
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
            'pdf_process_id' => 'required|exists:pdf_processes,id',
            'pdfs' => 'required|array',
            'pdfs.*' => 'required|file|mimes:pdf',
            'orders' => 'required|array',
            'orders.*' => 'required|integer',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $length = count($this->file('pdfs'));
        if ($length !== count($this->input('orders'))) {
            $validator->errors()->add('orders', 'The orders array must have the same length as the pdfs array.');
        }
    }
    
    
}
