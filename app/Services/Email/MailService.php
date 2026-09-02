<?php

namespace App\Services\Email;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends emails via Laravel's Mail system using SMTP config from .env.
 * Falls back to marking as "sent" if no mailer is configured.
 */
class MailService
{
    public function __construct(protected ActivityService $activities)
    {
    }

    public function isConfigured(): bool
    {
        return EmailAccount::where('is_active', true)->exists()
            || config('mail.mailers.smtp.host') !== null;
    }

    public function send(EmailMessage $email): void
    {
        try {
            if (! $email->lead->email) {
                $email->update(['status' => 'failed', 'delivery_status' => 'failed', 'error' => 'No recipient email known.']);
                return;
            }

            $fromEmail = config('mail.from.address');
            $fromName = config('mail.from.name');

            if ($fromEmail && config('mail.default') !== 'log') {
                // Send via SMTP
                Mail::html($email->body, function ($message) use ($email, $fromEmail, $fromName) {
                    $message->to($email->lead->email)
                        ->subject($email->subject)
                        ->from($fromEmail, $fromName);
                });

                $email->update([
                    'status' => 'sent',
                    'delivery_status' => 'sent',
                    'sent_at' => now(),
                ]);

                Log::info('[Mail] Email sent via SMTP', [
                    'to' => $email->lead->email,
                    'subject' => $email->subject,
                    'from' => $fromEmail,
                ]);
            } else {
                // Log mode or no SMTP — mark as sent in pipeline
                Log::info('[Mail] Email logged (no SMTP)', [
                    'to' => $email->lead->email,
                    'subject' => $email->subject,
                    'from' => $fromEmail ?? 'not set',
                ]);

                $email->update([
                    'status' => 'sent',
                    'delivery_status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

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