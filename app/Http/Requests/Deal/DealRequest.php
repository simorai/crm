<?php

namespace App\Http\Requests\Deal;

use Illuminate\Foundation\Http\FormRequest;

abstract class DealRequest extends FormRequest
{
    public function messages(): array
    {
        return [
            'entity_id.required' => 'The entity is required.',
            'entity_id.integer'  => 'The entity ID must be an integer.',
            'entity_id.exists'   => 'The selected entity does not exist.',
            'person_id.integer'  => 'The person ID must be an integer.',
            'person_id.exists'   => 'The selected person does not exist.',
            'owner_id.integer'   => 'The owner ID must be an integer.',
            'owner_id.exists'    => 'The selected owner does not exist.',
            'title.required'     => 'The title is required.',
            'title.max'          => 'The title may not be greater than 100 characters.',
            'value.numeric'      => 'The value must be a number.',
            'value.min'          => 'The value must be at least 0.',
            'stage.in'           => 'The stage must be one of: lead, contact, proposal, negotiation, won, or lost.',
            'probability.integer' => 'The probability must be an integer.',
            'probability.min'     => 'The probability must be at least 0%.',
            'probability.max'     => 'The probability may not be greater than 100%.',
            'expected_close_date.date' => 'Please enter a valid date for the expected close date.',
            'notes.max'          => 'The notes may not be greater than 1000 characters.',
        ];
    }
}