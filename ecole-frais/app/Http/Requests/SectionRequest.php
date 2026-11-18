<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100', Rule::unique('sections')->ignore($this->route('section'))],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
