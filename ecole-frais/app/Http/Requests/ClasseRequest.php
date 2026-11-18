<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
            'nom' => [
                'required', 'string', 'max:50',
                Rule::unique('classes')->where('section_id', $this->input('section_id'))->ignore($this->route('classe')),
            ],
            'niveau' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
