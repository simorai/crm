<?php

namespace App\Http\Requests\Entity;

class StoreEntityRequest extends EntityRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'vat'     => ['nullable', 'string', 'max:14', 'regex:/^([A-Z]{2})([A-Z0-9]{8,12})$/'],
            'email'   => ['nullable', 'email', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'address' => ['nullable', 'string', 'max:150'],
            'status'  => ['nullable', 'string', 'in:prospect,active,inactive,customer'],
        ];
    }

}
