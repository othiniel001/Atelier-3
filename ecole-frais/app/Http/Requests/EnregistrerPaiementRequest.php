<?php

namespace App\Http\Requests;

use App\Models\Paiement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnregistrerPaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eleve_id' => ['required', 'exists:eleves,id'],
            'frais_id' => ['required', 'exists:frais,id'],
            'montant' => ['required', 'integer', 'min:1'],
            'mode' => ['required', Rule::in(array_keys(Paiement::MODES))],
            'reference_externe' => ['nullable', 'required_unless:mode,especes', 'string', 'max:100'],
            'date_paiement' => ['nullable', 'date', 'before_or_equal:now'],
            'observation' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['reference_externe.required_unless' => 'La référence (transaction, chèque, virement) est obligatoire hors espèces.'];
    }
}
