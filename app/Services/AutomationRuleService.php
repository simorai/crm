<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Collection;

class AutomationRuleService
{
    /**
     * List all automation rules for the active tenant.
     */
    public function index(): Collection
    {
        return AutomationRule::orderBy('name')->get();
    }

    /**
     * Create a new automation rule.
     */
    public function create(array $data): AutomationRule
    {
        return AutomationRule::create($data);
    }

    /**
     * Update an automation rule.
     */
    public function update(AutomationRule $rule, array $data): AutomationRule
    {
        $rule->update($data);
        return $rule->fresh();
    }

    /**
     * Delete an automation rule.
     */
    public function delete(AutomationRule $rule): void
    {
        $rule->delete();
    }

    /**
     * Evaluate all active rules for a given trigger and deal.
     * Called after stage changes or other deal events.
     */
    public function evaluate(string $trigger, Deal $deal): void
    {
        $rules = AutomationRule::where('trigger', $trigger)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            if ($this->matchesConditions($rule, $deal)) {
                $this->executeActions($rule, $deal);
            }
        }
    }

    private function matchesConditions(AutomationRule $rule, Deal $deal): bool
    {
        if (empty($rule->conditions)) {
            return true;
        }

        foreach ($rule->conditions as $condition) {
            $field    = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value    = $condition['value'] ?? null;

            if (! $field || ! isset($deal->$field)) {
                continue;
            }

            $dealValue = $deal->$field;

            $match = match ($operator) {
                '='         => $dealValue == $value,
                '!='        => $dealValue != $value,
                '>'         => $dealValue > $value,
                '<'         => $dealValue < $value,
                '>='        => $dealValue >= $value,
                '<='        => $dealValue <= $value,
                'contains'  => str_contains((string) $dealValue, (string) $value),
                default     => false,
            };

            if (! $match) {
                return false;
            }
        }

        return true;
    }

    private function executeActions(AutomationRule $rule, Deal $deal): void
    {
        foreach ($rule->actions as $action) {
            $type = $action['type'] ?? null;

            match ($type) {
                'notify_owner' => $this->notifyOwner($deal, $action['message'] ?? 'Automation triggered.'),
                'update_stage' => isset($action['stage']) ? $deal->update(['stage' => $action['stage']]) : null,
                default        => null,
            };
        }
    }

    private function notifyOwner(Deal $deal, string $message): void
    {
        // In a full implementation, dispatch a NotifyUserJob or fire a Laravel Notification
        // For now, we log the automation event so it appears in the timeline
        \App\Models\ActivityLog::create([
            'tenant_id'     => $deal->tenant_id,
            'user_id'       => $deal->owner_id,
            'loggable_type' => Deal::class,
            'loggable_id'   => $deal->id,
            'type'          => 'other',
            'description'   => "Automation: {$message}",
        ]);
    }
}
