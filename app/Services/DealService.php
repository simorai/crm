<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DealService
{
    public function __construct(private ActivityLogService $activityLogService) {}

    // Default probability per stage
    private const STAGE_PROBABILITY = [
        'lead'       => 10,
        'contact'    => 20,
        'proposal'   => 40,
        'negotiation'=> 70,
        'won'        => 100,
        'lost'       => 0,
    ];

    public function kanban(array $filters = []): Collection
    {
        $query = Deal::with(['entity', 'person', 'owner'])
            ->when(isset($filters['owner_id']), fn ($q) => $q->where('owner_id', $filters['owner_id']))
            ->when(isset($filters['stage']),    fn ($q) => $q->where('stage', $filters['stage']))
            ->when(isset($filters['search']),   fn ($q) => $q->where('title', 'like', '%'.$filters['search'].'%'))
            ->when(isset($filters['date_from']),fn ($q) => $q->whereDate('expected_close_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']),  fn ($q) => $q->whereDate('expected_close_date', '<=', $filters['date_to']))
            ->when(isset($filters['min_value']),fn ($q) => $q->where('value', '>=', $filters['min_value']))
            ->when(isset($filters['max_value']),fn ($q) => $q->where('value', '<=', $filters['max_value']));

        return $query->get();
    }

    public function index(array $filters = []): LengthAwarePaginator
    {
        return Deal::with(['entity', 'person', 'owner'])
            ->when(isset($filters['owner_id']), fn ($q) => $q->where('owner_id', $filters['owner_id']))
            ->when(isset($filters['stage']),    fn ($q) => $q->where('stage', $filters['stage']))
            ->when(isset($filters['search']),   fn ($q) => $q->where('title', 'like', '%'.$filters['search'].'%'))
            ->paginate(20);
    }

    public function create(array $data): Deal
    {
        $data['probability'] = $data['probability']
            ?? self::STAGE_PROBABILITY[$data['stage'] ?? 'lead']
            ?? 0;

        return Deal::create($data);
    }

    public function show(Deal $deal): Deal
    {
        return $deal->load(['entity', 'person', 'owner', 'dealProducts.product']);
    }

    public function update(Deal $deal, array $data): Deal
    {
        if (isset($data['stage']) && ! isset($data['probability'])) {
            $data['probability'] = self::STAGE_PROBABILITY[$data['stage']] ?? $deal->probability;
        }

        $deal->update($data);
        return $deal->fresh(['entity', 'person', 'owner', 'dealProducts.product']);
    }

    public function updateStage(Deal $deal, string $stage): Deal
    {
        $oldStage = $deal->stage;

        $deal->update([
            'stage'       => $stage,
            'probability' => self::STAGE_PROBABILITY[$stage] ?? $deal->probability,
        ]);

        $this->activityLogService->log(
            $deal,
            'stage_change',
            "Stage changed from {$oldStage} to {$stage}",
            ['from' => $oldStage, 'to' => $stage]
        );

        return $deal->fresh();
    }

    public function delete(Deal $deal): void
    {
        $deal->delete();
    }
}
