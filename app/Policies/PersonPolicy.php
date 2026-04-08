<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    public function viewAny(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function view(User $user, Person $person): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($person->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function update(User $user, Person $person): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($person->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function delete(User $user, Person $person): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($person->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        $role = $user->tenants()
            ->where('tenants.id', app('current.tenant')->id)
            ->first()?->pivot?->role;
        return in_array($role, ['owner', 'admin']);
    }
}
