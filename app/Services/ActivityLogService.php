<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    /**
     * Log an activity on any model.
     *
     * @param  Model  $loggable  The subject being logged (Deal, Entity, Person, etc.)
     * @param  string  $type     Activity type (note, stage_change, email_sent, etc.)
     * @param  string  $description
     * @param  array   $metadata  Optional extra data
     * @param  int|null  $userId   The user who performed the action
     */
    public function log(
        Model $loggable,
        string $type,
        string $description,
        array $metadata = [],
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'tenant_id'    => app('current.tenant')->id,
            'user_id'      => $userId ?? auth()->id(),
            'loggable_type'=> $loggable::class,
            'loggable_id'  => $loggable->id,
            'type'         => $type,
            'description'  => $description,
            'metadata'     => $metadata ?: null,
        ]);
    }

    /**
     * Get paginated activity timeline for a given model.
     */
    public function timeline(Model $loggable, int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::with('user')
            ->where('loggable_type', $loggable::class)
            ->where('loggable_id', $loggable->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * List all activity logs for the active tenant (admin view).
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        return ActivityLog::with(['user', 'loggable'])
            ->when(isset($filters['loggable_type']), fn ($q) => $q->where('loggable_type', $filters['loggable_type']))
            ->when(isset($filters['loggable_id']),   fn ($q) => $q->where('loggable_id',   $filters['loggable_id']))
            ->when(isset($filters['type']),          fn ($q) => $q->where('type',          $filters['type']))
            ->when(isset($filters['user_id']),       fn ($q) => $q->where('user_id',       $filters['user_id']))
            ->latest()
            ->paginate(30);
    }
}
