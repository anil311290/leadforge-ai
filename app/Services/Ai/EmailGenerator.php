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
        $company = $lead->company;
        $website = $lead->website ?: ($lead->normalized_domain ? 'https://'.$lead->normalized_domain : null);
        $industry = $lead->industry ?? 'business';
        $service = $lead->recommended_service ?? 'custom software solutions';
        $summary = $lead->analysis['summary'] ?? '';
        $fromName = config('mail.from.name', config('leadforge.email.from_name', 'APARK IT Solutions'));
        $fromEmail = config('mail.from.address', config('leadforge.email.from_email', 'hello@example.com'));
        $product = config('leadforge.product', 'LeadForge AI');

        // Gather evidence from recommendations
        $evidencePoints = [];
        foreach ($lead->recommendations as $rec) {
            foreach ((array) ($rec->evidence ?? []) as $ev) {
                if (is_string($ev) && !str_starts_with($ev, 'Common service') && !str_starts_with($ev, 'Industry match')) {
                    $evidencePoints[] = $ev;
                }
            }
        }
        $evidence = !empty($evidencePoints) ? implode(', ', array_slice($evidencePoints, 0, 3)) : '';

        $prompt = <<<PROMPT
You are a senior business development professional at {$fromName}, a custom software agency.
Write a short, natural, personalised outreach email to {$company}.

**Context:**
- Company: {$company}
- Industry: {$industry}
- Website: {$website}
- What we noticed: {$summary}
- Service they likely need: {$service}
- Specific signals found: {$evidence}

**Rules:**
- Sound like a real human, NOT a robot or AI
- NEVER mention "lead data", "analysis", "opportunity score", or "basic analysis"
- Reference something specific about their business/industry
- Keep it short — 3-4 sentences max
- Offer value, don't just pitch
- End with a soft call to action (e.g. "worth a quick chat?")
- Sign with: {$fromName}

Return JSON with keys: subject, body
The body should be plain text, ready to send. No placeholders like [Your Name].
PROMPT;

        $raw = $this->ai->complete(
            'You are a senior business development professional. Write natural, human outreach emails.',
            $prompt,
            ['max_tokens' => 600, 'temperature' => 0.7]
        );

        $parsed = json_decode($raw, true);

        $body = $parsed['body'] ?? '';
        // Ensure signature is included
        if ($body && !str_contains($body, $fromName)) {
            $body .= "\n\n{$fromName}";
        }

        return [
            'subject' => $parsed['subject'] ?? 'Quick question for '.$company,
            'body' => $body,
        ];
    }

    protected function generateWithTemplate(Lead $lead): array
    {
        $fromName = config('mail.from.name', config('leadforge.email.from_name', 'APARK IT Solutions'));
        $service = $lead->recommended_service ?? 'custom software';
        $industry = $lead->industry ?? 'business';

        // Pick a specific observation
        $observation = '';
        if ($lead->website) {
            $observation = "I was looking at {$lead->website} and noticed you're in the {$industry} space";
        } else {
            $observation = "I came across {$lead->company} while researching the {$industry} space";
        }

        $body = <<<BODY
Hi there,

{$observation} — impressive what you're doing!

We help {$industry} companies streamline operations with {$service}. Thought I'd reach out to see if you're exploring ways to improve your current setup.

Worth a quick 10-minute chat sometime?

Best,
{$fromName}
BODY;

        return [
            'subject' => "Quick question for {$lead->company}",
            'body' => $body,
        ];
    }
}