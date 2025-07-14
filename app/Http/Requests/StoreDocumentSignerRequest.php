<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentSignerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $document = Document::find($this->document_id);
        return $document && $document->isMine($this->user());
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
            'email' => 'nullable|email',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }
} 