<?php

namespace App\Http\Requests\AutomationRule;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'trigger'     => ['required', 'string', 'in:deal_stage_changed,deal_created,deal_idle'],
            'conditions'  => ['nullable', 'array'],
            'actions'     => ['required', 'array'],
            'actions.*.type' => ['required', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
