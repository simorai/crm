<?php

namespace App\Http\Requests\CalendarEvent;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'            => ['sometimes', 'required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
            'location'         => ['nullable', 'string', 'max:100'],
            'start_at'         => ['sometimes', 'required', 'date'],
            'end_at'           => ['sometimes', 'required', 'date', 'after_or_equal:start_at'],
            'all_day'          => ['boolean'],
            'eventable_type'   => ['nullable', 'string', 'in:deal,entity,person'],
            'eventable_id'     => ['nullable', 'integer'],
            'attendees'        => ['nullable', 'array'],
            'attendees.*.type' => ['required_with:attendees', 'string', 'in:user,person'],
            'attendees.*.id'   => ['required_with:attendees', 'integer'],
        ];
    }
}
