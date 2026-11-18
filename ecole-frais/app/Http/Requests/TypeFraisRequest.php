<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TypeFraisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('types_frais')->ignore($this->route('typeFrais'))],
            'libelle' => ['required', 'string', 'max:100'],
            'obligatoire' => ['sometimes', 'boolean'],
        ];
    }
}
