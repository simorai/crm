<?php

namespace App\Http\Requests\Entity;

class UpdateEntityRequest extends EntityRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['sometimes', 'required', 'string', 'max:100'],
            'vat'     => ['sometimes', 'nullable', 'string', 'max:14', 'regex:/^([A-Z]{2})([A-Z0-9]{8,12})$/'],
            'email'   => ['sometimes', 'nullable', 'email', 'max:100'],
            'phone'   => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'address' => ['sometimes', 'nullable', 'string', 'max:150'],
            'status'  => ['sometimes', 'nullable', 'string', 'in:prospect,active,inactive,customer'],
        ];
    }
}
