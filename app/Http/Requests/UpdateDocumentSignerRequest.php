<?php

namespace App\Http\Requests;

use App\Models\DocumentSigner;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentSignerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $documentSigner = DocumentSigner::find($this->route('documentSigner'));
        return $documentSigner && $documentSigner->isOwnedBy($this->user());
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
            'email' => 'sometimes|email',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
        ];
    }
} 