<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EntityService
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = Entity::query()->withCount(['people']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vat', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function create(array $data): Entity
    {
        return Entity::create($data);
    }

    public function update(Entity $entity, array $data): Entity
    {
        $entity->update($data);
        return $entity->fresh();
    }

    public function delete(Entity $entity): void
    {
        $entity->delete();
    }

    public function show(Entity $entity): Entity
    {
        return $entity->loadCount('people');
    }
}
