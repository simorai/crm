<?php

namespace App\Http\Requests\Person;

use Illuminate\Foundation\Http\FormRequest;

abstract class PersonRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'name.required'    => 'The name is required.',
            'name.max'         => 'The name may not be greater than 100 characters.',
            'name.regex'       => 'The name may only contain letters, spaces, and hyphens.',
            'entity_id.exists' => 'The selected entity does not exist.',
            'email.max'        => 'The email may not be greater than 100 characters.',
            'email.email'      => 'Please enter a valid email address.',
            'phone.max'        => 'The phone number may not be greater than 20 digits.',
            'phone.regex'      => 'The phone number must contain only digits.',
            'position.max'     => 'The position may not be greater than 100 characters.',
            'position.regex'   => 'The position may only contain letters, spaces, and hyphens.',
            'notes.max'        => 'The notes may not be greater than 500 characters.',
        ];
    }
}
