<?php

namespace App\Http\Requests\LeadForm;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:150'],
            'fields'    => ['sometimes', 'array'],
            'fields.*'  => ['array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
