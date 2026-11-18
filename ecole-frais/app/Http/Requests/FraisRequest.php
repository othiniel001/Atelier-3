<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FraisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // On ignore les lignes d'échéance laissées vides dans le formulaire.
        $echeances = collect($this->input('echeances', []))
            ->filter(fn ($e) => filled($e['libelle'] ?? null) || filled($e['montant'] ?? null))
            ->values()
            ->all();

        $this->merge(['echeances' => $echeances]);
    }

    public function rules(): array
    {
        return [
            'type_frais_id' => ['required', 'exists:types_frais,id'],
            'classe_id' => ['required', 'exists:classes,id'],
            'annee_scolaire_id' => [
                'required', 'exists:annees_scolaires,id',
                Rule::unique('frais')
                    ->where('type_frais_id', $this->input('type_frais_id'))
                    ->where('classe_id', $this->input('classe_id'))
                    ->ignore($this->route('frais')),
            ],
            'montant' => ['required', 'integer', 'min:1'],
            'echeances' => ['array'],
            'echeances.*.libelle' => ['required', 'string', 'max:100'],
            'echeances.*.montant' => ['required', 'integer', 'min:1'],
            'echeances.*.date_limite' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return ['annee_scolaire_id.unique' => 'Ce type de frais est déjà défini pour cette classe et cette année.'];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $echeances = $this->input('echeances', []);
                if ($echeances !== [] && array_sum(array_column($echeances, 'montant')) != $this->input('montant')) {
                    $validator->errors()->add('echeances', 'La somme des échéances doit être égale au montant du frais.');
                }
            },
        ];
    }
}
