<?php

namespace App\Http\Requests\LeadForm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:150'],
            'fields'    => ['sometimes', 'array'],
            'fields.*'  => ['array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
