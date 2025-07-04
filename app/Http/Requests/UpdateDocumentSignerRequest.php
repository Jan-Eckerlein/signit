<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\DocumentSigner;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentSignerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $documentSigner = $this->route('document_signer');
        return $documentSigner && $documentSigner->isMine($this->user());
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
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
} 