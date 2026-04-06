<?php

namespace App\Http\Requests\EmailTemplate;


use Illuminate\Foundation\Http\FormRequest;

class StoreEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:100'],
            'body'    => ['required', 'string', 'max:500'],
            'type'    => ['sometimes', 'string', 'in:general,follow_up,proposal'],
        ];
    }
}
