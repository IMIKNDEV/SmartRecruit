<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavedFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'criteria' => ['sometimes', 'required', 'array'],
            'criteria.min_score' => ['nullable', 'numeric', 'between:0,100'],
            'criteria.tech_stack' => ['nullable', 'array'],
            'criteria.tech_stack.*' => ['string'],
            'criteria.contract_type' => ['nullable', 'string', 'in:CDI,CDD,Stage,Alternance,Freelance'],
            'criteria.status' => ['nullable', 'string', 'in:received,interview,accepted,refused'],
        ];
    }
}
