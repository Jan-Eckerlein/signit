<?php

namespace App\Http\Requests;

use App\Enums\Icon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentLogRequest extends FormRequest
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
            'contact_id' => 'nullable|exists:contacts,id',
            'document_id' => 'required|exists:documents,id',
            'ip' => 'nullable|string|max:45',
            'date' => 'required|date',
            'icon' => ['required', new Enum(Icon::class)],
            'text' => 'required|string',
        ];
    }
} 