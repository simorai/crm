<?php

namespace App\Http\Requests\EmailTemplate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['sometimes', 'string', 'max:150'],
            'subject' => ['sometimes', 'string', 'max:100'],
            'body'    => ['sometimes', 'string', 'max:500'],
            'type'    => ['sometimes', 'string', 'in:general,follow_up,proposal'],
        ];
    }
}
