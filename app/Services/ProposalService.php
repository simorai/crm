<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProposalService
{
    public function __construct(private ActivityLogService $activityLogService) {}

    /**
     * Store a proposal file and optionally send it via email.
     */
    public function upload(Deal $deal, UploadedFile $file, ?string $recipientEmail, ?string $emailBody): Deal
    {
        // Delete old proposal if exists
        if ($deal->proposal_path) {
            Storage::disk('private')->delete($deal->proposal_path);
        }

        $path = $file->store("proposals/tenant-{$deal->tenant_id}/deal-{$deal->id}", 'private');

        $deal->update(['proposal_path' => $path]);

        $this->activityLogService->log(
            $deal,
            'other',
            'Proposal file uploaded.',
            ['file_name' => $file->getClientOriginalName(), 'path' => $path]
        );

        return $deal->fresh();
    }

    /**
     * Send proposal file via email to the contact.
     */
    public function send(Deal $deal, string $recipientEmail, string $subject, string $body): void
    {
        if (! $deal->proposal_path || ! Storage::disk('private')->exists($deal->proposal_path)) {
            throw new \RuntimeException('No proposal file found for this deal.');
        }

        $filePath = Storage::disk('private')->path($deal->proposal_path);
        $fileName = basename($deal->proposal_path);

        Mail::send([], [], function ($message) use ($recipientEmail, $subject, $body, $filePath, $fileName) {
            $message->to($recipientEmail)
                ->subject($subject)
                ->html($body)
                ->attach($filePath, ['as' => $fileName]);
        });

        $deal->update(['proposal_sent_at' => now()]);

        $this->activityLogService->log(
            $deal,
            'email',
            "Proposal sent to {$recipientEmail}.",
            ['recipient' => $recipientEmail, 'subject' => $subject]
        );
    }
}
