<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'own_user_id' => 'required|exists:users,id',
            'knows_user_id' => 'nullable|exists:users,id',
            'knows_anonymous_users_id' => 'nullable|exists:anonymous_users,id',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ];
    }
} 