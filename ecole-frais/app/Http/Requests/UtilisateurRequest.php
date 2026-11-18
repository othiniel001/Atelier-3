<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creation = $this->route('utilisateur') === null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route('utilisateur'))],
            'telephone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [$creation ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }
}
