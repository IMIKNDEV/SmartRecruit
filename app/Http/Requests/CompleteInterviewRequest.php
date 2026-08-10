<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInterviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'score_technique' => ['required', 'integer', 'between:1,5'],
            'score_communication' => ['required', 'integer', 'between:1,5'],
            'score_motivation' => ['required', 'integer', 'between:1,5'],
        ];
    }
}
