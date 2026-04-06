<?php

namespace App\Http\Requests\Person;

class UpdatePersonRequest extends PersonRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'entity_id' => ['sometimes', 'nullable', 'integer', 'exists:entities,id'],
            'email'     => ['sometimes', 'nullable', 'email', 'max:100'],
            'phone'     => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'position'  => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'notes'     => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

}
