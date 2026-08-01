<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobOfferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'min:50'],
            'tech_stack' => ['sometimes', 'required', 'string', 'max:500'],
            'contract_type' => ['sometimes', 'required', 'string', 'in:CDI,CDD,Stage,Alternance,Freelance'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['sometimes', 'required', 'date', 'after:today'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
