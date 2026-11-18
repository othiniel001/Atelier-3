<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EleveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => ['required', 'string', 'max:30', Rule::unique('eleves')->ignore($this->route('eleve'))],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'sexe' => ['nullable', Rule::in(['M', 'F'])],
            'classe_id' => ['required', 'exists:classes,id'],
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'parent_eleve_id' => ['nullable', 'exists:parents_eleves,id'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }
}
