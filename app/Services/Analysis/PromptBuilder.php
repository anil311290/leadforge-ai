<?php

namespace App\Services\Analysis;

use App\Models\WebsiteScan;

class PromptBuilder
{
    /**
     * Build the AI analysis prompt from a website scan result.
     */
    public function build(WebsiteScan $scan): array
    {
        $businessData = $scan->business_data ?? [];
        $pages = $scan->pages()->limit(5)->get();
        $technologies = $scan->technologies()->limit(10)->get();
        $signals = $scan->signals()->limit(15)->get();

        $pageSummaries = $pages->map(fn ($p) => sprintf(
            "- %s (%s): %s",
            $p->url,
            $p->page_type ?? 'unknown',
            $p->meta_description ? substr($p->meta_description, 0, 200) : (substr($p->text_content ?? '', 0, 150))
        ))->implode("\n");

        $techList = $technologies->map(fn ($t) => sprintf(
            "- %s (%s) v%s",
            $t->name,
            $t->category ?? 'unknown',
            $t->version ?? 'N/A'
        ))->implode("\n");

        $signalList = $signals->map(fn ($s) => sprintf(
            "- [%s] %s: %s",
            $s->category ?? 'general',
            $s->signal,
            $s->detail ?? ''
        ))->implode("\n");

        $content = <<<PROMPT
Analyse this business website and return a JSON object with the following fields:

**Business Info:**
- industry (string): the most likely industry
- business_model (string): B2B, B2C, B2B2C, or marketplace
- summary (string): 2-3 sentence business summary

**Scoring (0-100):**
- score (int): overall opportunity score
- confidence (int): confidence in this analysis
- data_quality (int): quality of available data
- digital_maturity (int): 0-100 digital maturity score

**Contact Details (extract from the site if visible):**
- phone (string or null)
- email (string or null)
- address (string or null)
- contacts (array of objects with name, role, email, phone)

**Recommendations (array):**
Each recommendation should have:
- service_name (string): one of the services this business likely needs
- score (int): 0-100 fit score
- evidence (string): why this service fits
- estimated_min (float): minimum project value in INR
- estimated_max (float): maximum project value in INR

**Website Data:**
Domain: {$scan->domain}
Title: {$scan->title}
Description: {$scan->meta_description}
CMS: {$scan->cms}
E-commerce: {$scan->ecommerce_platform}
Page count: {$scan->page_count}
Data quality: {$scan->data_quality}/100

**Business Data from crawl:**
{$this->formatArray($businessData)}

**Key Pages:**
{$pageSummaries}

**Technologies Detected:**
{$techList}

**Signals Found:**
{$signalList}

Return ONLY valid JSON. No markdown, no code fences.
PROMPT;

        return [
            'content' => $content,
            'tokens_estimate' => str_word_count($content),
        ];
    }

    protected function formatArray(?array $data): string
    {
        if (! $data) {
            return 'None available.';
        }

        $lines = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $lines[] = "- {$key}: {$value}";
        }

        return implode("\n", $lines);
    }
}

