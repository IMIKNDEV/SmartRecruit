<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOfferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'tech_stack' => ['required', 'string', 'max:500'],
            'contract_type' => ['required', 'string', 'in:CDI,CDD,Stage,Alternance,Freelance'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['required', 'date', 'after:today'],
        ];
    }
}
