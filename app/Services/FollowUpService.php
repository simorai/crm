<?php

namespace App\Services;

use App\Jobs\FollowUpEmailJob;
use App\Models\Deal;
use App\Models\EmailTemplate;
use App\Models\FollowUpAutomation;

class FollowUpService
{
    public function __construct(private ActivityLogService $activityLogService) {}

    /**
     * Start a follow-up automation cycle for a deal.
     * Dispatches the first email in 48 business hours.
     */
    public function start(Deal $deal, int $emailTemplateId): FollowUpAutomation
    {
        // Cancel any existing active follow-up for this deal
        $this->cancel($deal);

        $followUp = FollowUpAutomation::create([
            'tenant_id'         => $deal->tenant_id,
            'deal_id'           => $deal->id,
            'email_template_id' => $emailTemplateId,
            'status'            => 'active',
            'template_index'    => 0,
            'emails_sent'       => 0,
            'next_send_at'      => $this->nextSendTime(),
        ]);

        FollowUpEmailJob::dispatch($followUp->id)->delay($followUp->next_send_at);

        return $followUp;
    }

    /**
     * Cancel all active follow-ups for a deal.
     */
    public function cancel(Deal $deal): void
    {
        FollowUpAutomation::where('deal_id', $deal->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Get follow-up status for a deal.
     */
    public function forDeal(Deal $deal): ?FollowUpAutomation
    {
        return FollowUpAutomation::with('emailTemplate')
            ->where('deal_id', $deal->id)
            ->latest()
            ->first();
    }

    /**
     * Execute the next email send for a follow-up automation.
     * Called by FollowUpEmailJob.
     */
    public function sendNext(FollowUpAutomation $followUp): void
    {
        if ($followUp->status !== 'active') {
            return;
        }

        $deal = $followUp->deal()->with('entity', 'person')->first();
        $template = $followUp->emailTemplate;

        if (! $deal || ! $template) {
            $followUp->update(['status' => 'cancelled']);
            return;
        }

        // Log the send activity
        $emailNumber = $followUp->emails_sent + 1;
        $this->activityLogService->log(
            $deal,
            'email',
            "Follow-up email #{$emailNumber} sent via template: {$template->name}",
            ['template_id' => $template->id, 'template_name' => $template->name]
        );

        $followUp->update([
            'emails_sent'    => $followUp->emails_sent + 1,
            'last_sent_at'   => now(),
            'template_index' => $followUp->template_index + 1,
            'next_send_at'   => $this->nextSendTime(),
        ]);

        // Schedule next send (max 5 emails per cycle)
        if ($followUp->emails_sent < 5) {
            FollowUpEmailJob::dispatch($followUp->id)->delay($followUp->next_send_at);
        } else {
            $followUp->update(['status' => 'completed']);
        }
    }

    private function nextSendTime(): \DateTimeInterface
    {
        $next = now()->addHours(48);
        // Skip to Monday if landing on weekend
        while ($next->isWeekend()) {
            $next = $next->addDay();
        }
        return $next->setHour(9)->setMinute(0)->setSecond(0);
    }

    /**
     * List all email templates for the active tenant.
     */
    public function templates(): \Illuminate\Database\Eloquent\Collection
    {
        return EmailTemplate::orderBy('name')->get();
    }

    /**
     * Create email template.
     */
    public function createTemplate(array $data): EmailTemplate
    {
        return EmailTemplate::create($data);
    }

    /**
     * Update email template.
     */
    public function updateTemplate(EmailTemplate $template, array $data): EmailTemplate
    {
        $template->update($data);
        return $template->fresh();
    }

    /**
     * Delete email template.
     */
    public function deleteTemplate(EmailTemplate $template): void
    {
        $template->delete();
    }
}
