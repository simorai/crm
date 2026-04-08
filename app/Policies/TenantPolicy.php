<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    /** Any authenticated user can list their own tenants. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** User can view a tenant they belong to. */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /** Any authenticated user can create a tenant. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Only the tenant owner or admin can update settings. */
    public function update(User $user, Tenant $tenant): bool
    {
        $role = $user->tenants()
            ->where('tenants.id', $tenant->id)
            ->first()?->pivot?->role;

        return in_array($role, ['owner', 'admin']);
    }

    /** Only the tenant owner can delete a tenant. */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $tenant->owner_id === $user->id;
    }
}
