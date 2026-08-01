<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'in:a_relancer,prioritaire,reserve,entretien_planifie'],
        ];
    }
}
