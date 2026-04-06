<?php

namespace App\Http\Requests\AutomationRule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:150'],
            'trigger'     => ['sometimes', 'string', 'in:deal_stage_changed,deal_created,deal_idle'],
            'conditions'  => ['nullable', 'array'],
            'actions'     => ['sometimes', 'array'],
            'actions.*.type' => ['required_with:actions', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
