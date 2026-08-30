<?php

namespace App\Services\FollowUp;

use App\Models\EmailMessage;
use App\Models\FollowUp;
use App\Models\Lead;

class FollowUpEngine
{
    public static function scheduleNext(int $leadId, ?int $lastMessageId = null): void
    {
        $lead = Lead::find($leadId);

        if (! $lead || in_array($lead->status, config('leadforge.followup.stop_on', []), true)) {
            return;
        }

        $days = config('leadforge.followup.days', [0, 3, 7, 14, 30]);
        $last = FollowUp::where('lead_id', $leadId)->orderByDesc('sequence_number')->first();
        $sequence = $last ? $last->sequence_number + 1 : 1;

        if ($sequence > count($days)) {
            return;
        }

        $dayGap = $days[$sequence - 1];

        FollowUp::create([
            'lead_id' => $leadId,
            'email_message_id' => $lastMessageId,
            'sequence_number' => $sequence,
            'content' => $lead->recommended_service ? "Follow-up {$sequence}: re-engage {$lead->company} on {$lead->recommended_service}." : "Follow-up {$sequence}: re-engage {$lead->company}.",
            'scheduled_at' => now()->addDays($dayGap > 0 ? $dayGap : 0)->setTime(10, 0),
            'status' => 'pending',
        ]);
    }

    /**
     * Send any due follow-ups (called by the scheduled task / worker).
     */
    public static function processDue(): int
    {
        $due = FollowUp::where('status', 'pending')->where('scheduled_at', '<=', now())->get();

        foreach ($due as $followUp) {
            self::sendFollowUp($followUp);
        }

        return $due->count();
    }

    public static function sendFollowUp(FollowUp $followUp): void
    {
        $lead = $followUp->lead;

        if (! $lead || in_array($lead->status, config('leadforge.followup.stop_on', []), true)) {
            $followUp->update(['status' => 'cancelled']);

            return;
        }

        $message = EmailMessage::create([
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'direction' => 'outbound',
            'subject' => 'Re: '.($lead->company).' — quick question',
            'body' => $followUp->content,
            'to_email' => $lead->email,
            'from_email' => config('leadforge.email.from_email'),
            'status' => 'sent',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $followUp->update(['status' => 'sent', 'sent_at' => now(), 'email_message_id' => $message->id]);

        self::scheduleNext((int) $lead->id, $message->id);
    }
}