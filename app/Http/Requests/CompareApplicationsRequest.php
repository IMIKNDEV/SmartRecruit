<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompareApplicationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isRecruiter();
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:2', 'max:4'],
            'ids.*' => ['integer', 'exists:applications,id'],
        ];
    }
}
