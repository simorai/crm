<?php

namespace App\Http\Requests\ActivityLog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'loggable_type' => ['required', 'string', 'in:App\Models\Deal,App\Models\Entity,App\Models\Person,App\Models\CalendarEvent'],
            'loggable_id'   => ['required', 'integer'],
            'type'          => ['required', 'string', 'in:note,call,email,meeting,stage_change,task,other'],
            'description'   => ['required', 'string', 'max:2000'],
            'metadata'      => ['nullable', 'array'],
        ];
    }
}
