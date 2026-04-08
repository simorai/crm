<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    private function sameTenant(User $user, Product $product): bool
    {
        $tenant = app('current.tenant');
        return $tenant && $product->tenant_id === $tenant->id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        if (!$this->sameTenant($user, $product)) {
            abort(404);
        }
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Product $product): bool
    {
        if (!$this->sameTenant($user, $product)) {
            abort(404);
        }
        return true;
    }

    public function delete(User $user, Product $product): bool
    {
        if (!$this->sameTenant($user, $product)) {
            abort(404);
        }
        return true;
    }
}
