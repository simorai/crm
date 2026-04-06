<?php

namespace App\Http\Requests\Entity;

use Illuminate\Foundation\Http\FormRequest;

abstract class EntityRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'name.required'  => 'The name is required.',
            'name.max'       => 'The name may not be greater than 100 characters.',
            'name.regex'     => 'The name may only contain letters, spaces, and hyphens.',
            'vat.max'        => 'The VAT number may not be greater than 14 characters.',
            'vat.regex'      => 'The VAT number must start with a country code (2 uppercase letters) followed by 8–12 alphanumeric characters.',
            'email.max'      => 'The email may not be greater than 100 characters.',
            'email.email'    => 'Please enter a valid email address.',
            'phone.max'      => 'The phone number may not be greater than 20 digits.',
            'phone.regex'    => 'The phone number must contain only digits.',
            'address.max'    => 'The address may not be greater than 150 characters.',
            'status.in'      => 'The status must be one of: prospect, active, inactive, or customer.',
        ];
    }
}