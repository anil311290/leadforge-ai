<?php

namespace App\Jobs;

use App\Models\EmailMessage;
use App\Models\Lead;
use App\Services\Ai\EmailGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateEmail implements ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public function __construct(public Lead $lead)
    {
    }

    public function handle(EmailGenerator $generator): void
    {
        $draft = $generator->generate($this->lead);

        EmailMessage::create([
            'lead_id' => $this->lead->id,
            'campaign_id' => $this->lead->campaign_id,
            'direction' => 'outbound',
            'subject' => $draft['subject'] ?? 'Service opportunity for '.$this->lead->company,
            'body' => $draft['body'] ?? '',
            'to_email' => $this->lead->email,
            'from_email' => config('mail.from.address', config('leadforge.email.from_email')),
            'status' => config('leadforge.email.require_approval', true) ? 'pending_approval' : 'approved',
        ]);
    }
}