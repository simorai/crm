<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Support\Str;

class TenantService
{
    public function create(User $owner, array $data): Tenant
    {
        $slug = Str::slug($data['slug'] ?? $data['name']);

        $tenant = Tenant::create([
            'name'     => $data['name'],
            'slug'     => $slug,
            'owner_id' => $owner->id,
            'settings' => $data['settings'] ?? null,
        ]);

        // Attach the owner as a tenant member with role "owner"
        TenantUser::create([
            'tenant_id' => $tenant->id,
            'user_id'   => $owner->id,
            'role'      => 'owner',
        ]);

        return $tenant;
    }

    public function listForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->tenants()->withPivot('role')->get();
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update(array_filter([
            'name'     => $data['name'] ?? null,
            'settings' => $data['settings'] ?? null,
        ]));

        return $tenant->fresh();
    }

    public function switchActiveTenant(User $user, string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        if (! $user->tenants()->where('tenants.id', $tenant->id)->exists()) {
            abort(403, 'Access denied to this tenant.');
        }

        session(['active_tenant' => $tenant->slug]);

        return $tenant;
    }
}
