<?php

namespace App\Services\Ai;

use App\Models\Lead;

class EmailGenerator
{
    public function __construct(protected AiClient $ai)
    {
    }

    /**
     * Generate a personalised outbound email draft for a lead.
     * Returns ['subject' => string, 'body' => string].
     */
    public function generate(Lead $lead): array
    {
        if ($this->ai->isConfigured()) {
            return $this->generateWithAi($lead);
        }

        return $this->generateWithTemplate($lead);
    }

    protected function generateWithAi(Lead $lead): array
    {
        $analysis = $lead->analysis['summary'] ?? 'your business';
        $service = $lead->recommended_service ?? 'custom software solutions';

        $prompt = "[Situation]\nCompany: {$lead->company}. Website: {$lead->website}. ".
            "Observed findings: {$analysis}. Recommended service: {$service}.\n\n".
            "Write a short, professional, non-pushy outreach email in JSON with keys: subject, greeting, body, closing.";

        $raw = $this->ai->complete('You are a senior outbound sales copywriter.', $prompt, [
            'max_tokens' => 600,
        ]);

        $parsed = json_decode($raw, true);

        return [
            'subject' => $parsed['subject'] ?? 'Service opportunity for '.$lead->company,
            'body' => ($parsed['greeting'] ?? '')."\n\n".($parsed['body'] ?? '')."\n\n".($parsed['closing'] ?? ''),
        ];
    }

    protected function generateWithTemplate(Lead $lead): array
    {
        $pain = '';
        if ($top = $lead->recommendations->first()) {
            $pain = implode(', ', array_slice((array) $top->evidence, 0, 2));
        }

        $body = "Hi there,\n\n"
            ."While researching {$lead->company}, I noticed {$this->helpfulInsight($lead)}.\n\n"
            ."We are a software agency focused on " . ($lead->recommended_service ?? 'custom software') . " for growing businesses. "
            ."I'd be happy to share how companies in your space have improved operations and reduced manual work.\n\n"
            ."Would a brief 15-minute call next week be convenient?\n\n"
            ."Best regards,\n"
            .config('leadforge.email.from_name', 'APARK IT Solutions');

        return [
            'subject' => 'Helping '.$lead->company.' with '.($lead->recommended_service ?? 'software').' improvements',
            'body' => $body,
        ];
    }

    protected function helpfulInsight(Lead $lead): string
    {
        if ($lead->recommended_service) {
            return 'you may benefit from '.$lead->recommended_service;
        }

        return 'there may be opportunities to streamline operations with custom software';
    }
}