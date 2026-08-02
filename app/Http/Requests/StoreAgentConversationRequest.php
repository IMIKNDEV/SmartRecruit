<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentConversationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'context_type' => ['required', 'string', 'in:matching,interview_questions,general'],
            'context_id' => ['nullable', 'integer', 'exists:applications,id'],
        ];
    }
}
