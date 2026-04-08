<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function view(User $user, Deal $deal): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($deal->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function update(User $user, Deal $deal): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($deal->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function delete(User $user, Deal $deal): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($deal->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        $role = $user->tenants()
            ->where('tenants.id', app('current.tenant')->id)
            ->first()?->pivot?->role;
        return in_array($role, ['owner', 'admin']);
    }
}
