<?php

namespace App\Http\Requests\AiSuggestion;

use Illuminate\Foundation\Http\FormRequest;

class SmartChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages'           => ['required', 'array', 'min:1', 'max:50'],
            'messages.*.role'    => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ];
    }
}