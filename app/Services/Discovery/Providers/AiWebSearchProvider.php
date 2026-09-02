<?php

namespace App\Services\Discovery\Providers;

use App\Services\Discovery\Contracts\DiscoveryProvider;
use App\Services\Ai\AiClient;
use Illuminate\Support\Facades\Log;

/**
 * AI-powered web search discovery provider.
 * Uses AI (OpenRouter/OpenAI) to search the web and discover businesses
 * based on location, industry, and service needs — just like Google/LinkedIn search.
 */
class AiWebSearchProvider implements DiscoveryProvider
{
    public function __construct(
        protected AiClient $ai
    ) {
    }

    public function name(): string
    {
        return 'ai_web_search';
    }

    public function isConfigured(): bool
    {
        return $this->ai->isConfigured();
    }

    public function discover(string $location, array $options = []): array
    {
        $max = $options['max'] ?? 20;
        $query = $options['query'] ?? config('leadforge.discovery.search_api.default_query', 'software companies');

        Log::info("[AiWebSearch] Searching for {$query} in {$location} (max: {$max})");

        $prompt = <<<PROMPT
You are a business research assistant. Search the web for real businesses in "{$location}" that likely need IT/software services.

**Search Criteria:**
- Location: {$location}
- Industry/Type: {$query}
- Target: Growing businesses, SMEs, startups, or established companies that may need:
  - Website development or redesign
  - Mobile apps
  - CRM / Automation
  - Custom software
  - AI integration
  - E-commerce solutions
  - WhatsApp automation
  - UI/UX redesign

**Return a JSON object with a "businesses" array.**
Each business must have:
- name (string, required): Realistic business name
- website (string or null): Their website URL if known
- industry (string): Their industry sector
- location (string): Specific city/area within {$location}
- phone (string or null): Contact phone if known
- email (string or null): Contact email if known
- description (string): 1-2 sentence description of what they do
- why_need_software (string): Why this business likely needs IT/software services

Generate {$max} realistic businesses. Return ONLY valid JSON. No markdown.
PROMPT;

        try {
            $raw = $this->ai->complete(
                'You are a business research and lead generation engine.',
                $prompt,
                ['max_tokens' => 4000, 'temperature' => 0.7]
            );

            $parsed = json_decode($raw, true);
            $businesses = $parsed['businesses'] ?? [];

            if (empty($businesses)) {
                Log::warning("[AiWebSearch] AI returned empty results for {$location}");
                return [];
            }

            $results = [];
            foreach ($businesses as $biz) {
                $name = $biz['name'] ?? null;
                if (! $name) {
                    continue;
                }

                $website = $biz['website'] ?? null;
                // Clean up website URL
                if ($website && ! str_starts_with($website, 'http')) {
                    $website = 'https://' . $website;
                }

                $results[] = [
                    'name' => $name,
                    'website' => $website,
                    'industry' => $biz['industry'] ?? null,
                    'location' => $biz['location'] ?? $location,
                    'city' => $biz['location'] ?? $location,
                    'phone' => $biz['phone'] ?? null,
                    'email' => $biz['email'] ?? null,
                    'description' => $biz['description'] ?? null,
                    'why_need_software' => $biz['why_need_software'] ?? null,
                ];
            }

            Log::info("[AiWebSearch] Found " . count($results) . " businesses in {$location}");

            return $results;
        } catch (\Throwable $e) {
            Log::error("[AiWebSearch] Failed: " . $e->getMessage());
            throw new \RuntimeException('AI web search failed: ' . $e->getMessage());
        }
    }
}