<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

class EntityPolicy
{
    /** Any member of the active tenant can list entities. */
    public function viewAny(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    /** Any member of the active tenant can view an entity (HasTenant scope enforces isolation). */
    public function view(User $user, Entity $entity): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($entity->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    /** Any member can create an entity within the current tenant. */
    public function create(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    /** Any member can update an entity. */
    public function update(User $user, Entity $entity): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($entity->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    /** Only owner/admin can delete. */
    public function delete(User $user, Entity $entity): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($entity->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        $role = $user->tenants()
            ->where('tenants.id', app('current.tenant')->id)
            ->first()?->pivot?->role;
        return in_array($role, ['owner', 'admin']);
    }
}
