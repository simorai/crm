<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function viewAny(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function view(User $user, CalendarEvent $calendarEvent): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($calendarEvent->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function update(User $user, CalendarEvent $calendarEvent): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($calendarEvent->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function delete(User $user, CalendarEvent $calendarEvent): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($calendarEvent->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        // Only owner/admin can delete
        $role = $user->tenants()
            ->where('tenants.id', app('current.tenant')->id)
            ->first()?->pivot?->role;
        return in_array($role, ['owner', 'admin']);
    }
}
