<?php

namespace App\Http\Requests\AiSuggestion;

use Illuminate\Foundation\Http\FormRequest;

class PostponeAiSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ];
    }
}