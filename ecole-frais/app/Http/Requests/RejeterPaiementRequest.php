<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejeterPaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['motif_rejet' => ['required', 'string', 'min:5', 'max:255']];
    }
}
