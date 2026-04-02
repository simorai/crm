<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PersonService
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = Person::query()->with('entity:id,name');

        if (! empty($filters['entity_id'])) {
            $query->where('entity_id', $filters['entity_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function create(array $data): Person
    {
        return Person::create($data);
    }

    public function update(Person $person, array $data): Person
    {
        $person->update($data);
        return $person->fresh(['entity']);
    }

    public function delete(Person $person): void
    {
        $person->delete();
    }

    public function show(Person $person): Person
    {
        return $person->load('entity:id,name');
    }
}
