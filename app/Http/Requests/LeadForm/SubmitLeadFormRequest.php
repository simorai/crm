<?php

namespace App\Http\Requests\LeadForm;

use Illuminate\Foundation\Http\FormRequest;

class SubmitLeadFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['sometimes', 'string', 'max:150'],
            'email'   => ['sometimes', 'email', 'max:255'],
            'phone'   => ['sometimes', 'string', 'max:50'],
            'company' => ['sometimes', 'string', 'max:150'],
            'message' => ['sometimes', 'string', 'max:2000'],
            'budget'  => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function sanitized(): array
    {
        return array_map(
            fn ($v) => is_string($v) ? strip_tags(trim($v)) : $v,
            $this->validated()
        );
    }
}
