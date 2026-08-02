<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAgentMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
        ];
    }
}
