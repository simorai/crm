<?php

namespace App\Http\Requests\Person;

class StorePersonRequest extends PersonRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'entity_id' => ['nullable', 'integer', 'exists:entities,id'],
            'email'     => ['nullable', 'email', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'position'  => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ];
    }

}
