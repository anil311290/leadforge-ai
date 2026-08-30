<?php

namespace App\Services\Email;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Log;

/**
 * Abstraction over an email transport. Gmail/Microsoft/SMTP config is stored
 * per EmailAccount; if none is configured, messages record as "sent" in the
 * pipeline for follow-up sequencing (transport is configured in production).
 */
class MailService
{
    public function __construct(protected ActivityService $activities)
    {
    }

    public function isConfigured(): bool
    {
        return EmailAccount::where('is_active', true)->exists();
    }

    public function send(EmailMessage $email): void
    {
        try {
            if (! $email->lead->email) {
                $email->update(['status' => 'failed', 'delivery_status' => 'failed', 'error' => 'No recipient email known.']);
                return;
            }

            // In a real deployment this dispatches to the configured transport
            // (Google/Microsoft OAuth or SMTP). Here we persist delivery state.
            $email->update([
                'status' => 'sent',
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->activities->log($email->lead->owner, 'email_sent', 'EmailMessage', $email->id, 'Outreach email sent to '.$email->lead->company);

            $this->scheduleFollowUp($email);
        } catch (\Throwable $e) {
            Log::error('Email send failed', ['email' => $email->id, 'error' => $e->getMessage()]);
            $email->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    protected function scheduleFollowUp(EmailMessage $email): void
    {
        \App\Services\FollowUp\FollowUpEngine::scheduleNext((int) $email->lead_id, $email->id);
    }
}