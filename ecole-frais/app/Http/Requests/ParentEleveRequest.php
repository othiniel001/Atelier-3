<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParentEleveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parent = $this->route('parent');

        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9 ]{8,20}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:255'],
            // Création optionnelle d'un compte pour l'espace parent.
            'creer_compte' => ['sometimes', 'boolean'],
            'mot_de_passe' => ['nullable', 'required_if:creer_compte,1', 'string', 'min:8'],
            'email_compte' => [
                'nullable', 'required_if:creer_compte,1', 'email',
                Rule::unique('users', 'email')->ignore($parent?->user_id),
            ],
        ];
    }
}
