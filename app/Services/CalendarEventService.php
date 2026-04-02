<?php

namespace App\Services;

use App\Jobs\SendEventReminderJob;
use App\Models\CalendarEvent;
use App\Models\CalendarEventAttendee;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarEventService
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        return CalendarEvent::with(['owner', 'attendees'])
            ->when(isset($filters['start']), fn ($q) => $q->where('start_at', '>=', $filters['start']))
            ->when(isset($filters['end']),   fn ($q) => $q->where('end_at',   '<=', $filters['end']))
            ->when(isset($filters['search']),fn ($q) => $q->where('title', 'like', '%'.$filters['search'].'%'))
            ->orderBy('start_at')
            ->paginate(50);
    }

    public function create(array $data): CalendarEvent
    {
        $attendees = $data['attendees'] ?? [];
        unset($data['attendees']);

        $event = CalendarEvent::create($data);

        $this->syncAttendees($event, $attendees);
        $this->scheduleReminder($event);

        return $event->load(['owner', 'attendees']);
    }

    public function show(CalendarEvent $event): CalendarEvent
    {
        return $event->load(['owner', 'attendees.attendee', 'eventable']);
    }

    public function update(CalendarEvent $event, array $data): CalendarEvent
    {
        $attendees = $data['attendees'] ?? null;
        unset($data['attendees']);

        $event->update($data);

        if ($attendees !== null) {
            $this->syncAttendees($event, $attendees);
        }

        return $event->fresh(['owner', 'attendees']);
    }

    public function delete(CalendarEvent $event): void
    {
        $event->attendees()->delete();
        $event->delete();
    }

    private function syncAttendees(CalendarEvent $event, array $attendees): void
    {
        // attendees: [{ type: 'user'|'person', id: int }, ...]
        $event->attendees()->delete();

        foreach ($attendees as $attendee) {
            CalendarEventAttendee::create([
                'calendar_event_id' => $event->id,
                'attendee_type'     => $attendee['type'] === 'user'
                    ? \App\Models\User::class
                    : \App\Models\Person::class,
                'attendee_id'       => $attendee['id'],
            ]);
        }
    }

    private function scheduleReminder(CalendarEvent $event): void
    {
        // Dispatch reminder 30 minutes before event
        $reminderAt = $event->start_at->subMinutes(30);

        if ($reminderAt->isFuture()) {
            SendEventReminderJob::dispatch($event)->delay($reminderAt);
        }
    }
}
