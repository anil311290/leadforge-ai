<?php

namespace App\Services\Analysis;

use App\Models\AiAnalysis;
use App\Models\AiRecommendation;
use App\Models\AiUsageLog;
use App\Models\Lead;
use App\Models\LeadOpportunity;
use App\Models\Service;
use App\Models\WebsiteScan;
use App\Services\Ai\AiClient;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Log;

class AnalysisService
{
    public function __construct(
        protected PromptBuilder $prompts,
        protected RuleScorer $scorer,
        protected ProjectValueEstimator $values,
        protected AiClient $ai,
        protected ActivityService $activities
    ) {
    }

    public function analyseLead(Lead $lead, ?WebsiteScan $scan = null): void
    {
        $scan = $scan ?: $lead->latestScan;

        // Treat failed scans or scans with no data as "no scan"
        $hasUsableScan = $scan && $scan->status === 'completed' && $scan->data_quality > 0;

        $start = microtime(true);

        try {
            $result = $this->ai->isConfigured() && $hasUsableScan
                ? $this->runAiAnalysis($lead, $scan)
                : $this->runHeuristicAnalysis($lead, $hasUsableScan ? $scan : null);

            if (empty($result['recommendations']) && $this->ai->isConfigured()) {
                $result['recommendations'] = $this->runHeuristicAnalysis($lead, null)['recommendations'];
            }

            $this->persist($lead, $hasUsableScan ? $scan : null, $result, round((microtime(true) - $start) * 1000));
            $this->activities->log($lead->owner, 'analysis_completed', 'Lead', $lead->id, 'Analysis completed for '.$lead->company);
        } catch (\Throwable $e) {
            Log::error('Lead analysis failed', ['lead' => $lead->id, 'error' => $e->getMessage()]);
            // Fallback to heuristic even on AI failure
            try {
                $result = $this->runHeuristicAnalysis($lead, null);
                $this->persist($lead, null, $result, round((microtime(true) - $start) * 1000));
            } catch (\Throwable $e2) {
                Log::error('Heuristic analysis also failed', ['lead' => $lead->id, 'error' => $e2->getMessage()]);
                $lead->update(['status' => Lead::STATUS_ANALYZED, 'analysis' => ['error' => $e->getMessage()]]);
            }
        }
    }

    protected function runAiAnalysis(Lead $lead, ?WebsiteScan $scan = null): array
    {
        if (! $scan) {
            // No scan data — use lead data only
            return $this->runHeuristicAnalysis($lead, null);
        }

        $built = $this->prompts->build($scan);

        $raw = $this->ai->complete('You are an exact business analysis engine.', $built['content'], [
            'max_tokens' => (int) config('leadforge.ai.max_tokens', 2000),
        ]);

        $parsed = json_decode($raw, true);

        return [
            'score' => (int) ($parsed['score'] ?? 0),
            'confidence' => (int) ($parsed['confidence'] ?? 0),
            'data_quality' => (int) ($parsed['data_quality'] ?? 0),
            'digital_maturity' => (int) ($parsed['digital_maturity'] ?? 0),
            'industry' => $parsed['industry'] ?? null,
            'business_model' => $parsed['business_model'] ?? null,
            'summary' => $parsed['summary'] ?? null,
            'recommendations' => $this->hydrateRecommendations($parsed['recommendations'] ?? []),
            'raw_input' => $built['content'],
            'provider' => config('leadforge.ai.provider'),
            'model' => config('leadforge.ai.model'),
        ];
    }

    protected function runHeuristicAnalysis(Lead $lead, ?WebsiteScan $scan = null): array
    {
        if (! $scan) {
            // No scan data — score based on lead info alone, with basic service matching
            $services = Service::where('is_active', true)->get();
            $recommendations = [];
            $industry = strtolower($lead->industry ?? '');

            foreach ($services as $service) {
                $score = 0;
                $evidence = [];

                // Match service name/category against lead industry
                $serviceKeywords = strtolower($service->name.' '.$service->category);
                if ($industry && (
                    str_contains($serviceKeywords, $industry)
                    || str_contains($industry, strtolower($service->category))
                )) {
                    $score = 40;
                    $evidence[] = 'Industry match: '.$lead->industry;
                }

                // Generic services always get a base score
                if (in_array($service->slug, ['custom-software', 'website-e-commerce', 'crm-automation'])) {
                    $score = max($score, 25);
                    if (empty($evidence)) {
                        $evidence[] = 'Common service for growing businesses';
                    }
                }

                if ($score > 0) {
                    [$min, $max] = array_values($this->values->estimate($service, ['digital_maturity' => 30]));
                    $recommendations[] = [
                        'service' => $service->name,
                        'service_name' => $service->name,
                        'service_id' => $service->id,
                        'score' => $score,
                        'confidence' => 30,
                        'evidence' => $evidence,
                        'inference' => 'Based on lead profile (no website scan)',
                        'recommendation' => 'Consider outreach to discuss '.$service->name.' needs.',
                        'estimated_min' => $min,
                        'estimated_max' => $max,
                    ];
                }
            }

            // Sort by score descending
            usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

            $topScore = $recommendations[0]['score'] ?? 0;

            return [
                'score' => $topScore,
                'confidence' => 30,
                'data_quality' => 20,
                'digital_maturity' => 30,
                'industry' => $lead->industry,
                'business_model' => null,
                'summary' => 'Basic analysis based on lead data (no website scan available).',
                'recommendations' => $recommendations,
                'raw_input' => null,
                'provider' => null,
                'model' => null,
            ];
        }

        $opportunities = $this->scorer->score($scan);

        $recommendations = [];
        foreach ($opportunities as $opp) {
            if ($opp['score'] < 30) {
                continue;
            }
            [$min, $max] = [0, 0];
            if ($service = Service::where('name', $opp['service_name'])->first()) {
                [$min, $max] = array_values($this->values->estimate($service, ['digital_maturity' => $scan->data_quality]));
            }
            $recommendations[] = [
                'service' => $opp['service_name'],
                'service_name' => $opp['service_name'],
                'service_id' => $service?->id,
                'score' => $opp['score'],
                'confidence' => $opp['confidence'],
                'evidence' => array_map(fn ($e) => $e['signal'], $opp['evidence'] ?? []),
                'inference' => 'Detected by rule engine from observed signals',
                'recommendation' => 'Position around the observed technology/pain gap.',
                'estimated_min' => $min,
                'estimated_max' => $max,
            ];
        }

        $top = $recommendations[0]['score'] ?? 0;

        return [
            'score' => (int) ($top !== 0 ? min(100, $top) : $scan->data_quality),
            'confidence' => (int) ($recommendations[0]['confidence'] ?? 50),
            'data_quality' => $scan->data_quality,
            'digital_maturity' => $scan->data_quality,
            'industry' => $lead->industry,
            'business_model' => null,
            'summary' => sprintf('Rule-based analysis. %d matching opportunities found.', count($recommendations)),
            'recommendations' => $recommendations,
            'raw_input' => null,
            'provider' => null,
            'model' => null,
        ];
    }

    protected function hydrateRecommendations(array $items): array
    {
        return array_map(fn ($item) => $this->hydrateRecommendation($item), $items);
    }

    protected function hydrateRecommendation(array $item): array
    {
        $service = Service::where('name', $item['service'] ?? '')->first();
        [$min, $max] = [(int) ($item['estimated_min'] ?? 0), (int) ($item['estimated_max'] ?? 0)];

        if ($service && ($min === 0 && $max === 0)) {
            [$min, $max] = array_values($this->values->estimate($service, []));
        }
        if ($service && $min === 0) {
            $min = (int) round($service->min_value / 1000) * 1000;
        }
        if ($service && $max === 0) {
            $max = (int) round($service->max_value / 1000) * 1000;
        }

        return [
            'service' => $item['service_name'] ?? $item['service'] ?? '',
            'service_name' => $item['service_name'] ?? $item['service'] ?? '',
            'service_id' => $item['service_id'] ?? $service?->id,
            'score' => (int) ($item['score'] ?? 0),
            'confidence' => (int) ($item['confidence'] ?? 0),
            'evidence' => $item['evidence'] ?? [],
            'inference' => $item['inference'] ?? null,
            'recommendation' => $item['recommendation'] ?? null,
            'estimated_min' => $min,
            'estimated_max' => $max,
        ];
    }

    protected function persist(Lead $lead, ?WebsiteScan $scan, array $result, int $durationMs): void
    {
        $top = $result['recommendations'][0] ?? null;
        $leadScore = $result['score'] ?? ($top['score'] ?? 0);

        $analysis = AiAnalysis::create([
            'lead_id' => $lead->id,
            'scan_id' => $scan?->id,
            'model' => $result['model'] ?? null,
            'provider' => $result['provider'] ?? null,
            'input' => ['prompt' => $result['raw_input']],
            'output' => $result,
            'score' => $leadScore,
            'confidence' => $result['confidence'] ?? 0,
            'duration_ms' => $durationMs,
            'status' => $result['provider'] ? 'completed' : 'heuristic',
        ]);

        AiRecommendation::where('lead_id', $lead->id)->delete();
        LeadOpportunity::where('lead_id', $lead->id)->delete();

        $serviceNames = [];
        foreach ($result['recommendations'] as $rec) {
            AiRecommendation::create([
                'lead_id' => $lead->id,
                'ai_analysis_id' => $analysis->id,
                'service_id' => $rec['service_id'] ?? null,
                'service_name' => $rec['service_name'] ?? $rec['service'],
                'score' => $rec['score'],
                'confidence' => $rec['confidence'],
                'evidence' => $rec['evidence'] ?? [],
                'inference' => $rec['inference'] ?? null,
                'recommendation' => $rec['recommendation'] ?? null,
                'estimated_min' => $rec['estimated_min'] ?? 0,
                'estimated_max' => $rec['estimated_max'] ?? 0,
            ]);

            if ($rec['service_id']) {
                LeadOpportunity::create([
                    'lead_id' => $lead->id,
                    'service_id' => $rec['service_id'],
                    'service_name' => $rec['service_name'] ?? $rec['service'],
                    'score' => $rec['score'],
                    'confidence' => $rec['confidence'],
                    'evidence' => $rec['evidence'] ?? [],
                    'inference' => $rec['inference'] ?? null,
                    'recommendation' => $rec['recommendation'] ?? null,
                    'estimated_min' => $rec['estimated_min'] ?? 0,
                    'estimated_max' => $rec['estimated_max'] ?? 0,
                ]);
            }

            $serviceNames[] = $rec['service_name'] ?? $rec['service'];
        }

        $lead->forceFill([
            'opportunity_score' => $leadScore,
            'confidence' => $result['confidence'] ?? 0,
            'data_quality' => $result['data_quality'] ?? 0,
            'digital_maturity' => $result['digital_maturity'] ?? 0,
            'industry' => $result['industry'] ?? $lead->industry,
            'business_model' => $result['business_model'] ?? $lead->business_model,
            'estimated_min' => $top['estimated_min'] ?? 0,
            'estimated_max' => $top['estimated_max'] ?? 0,
            'recommended_service' => $top['service_name'] ?? $top['service'] ?? null,
            'recommended_services' => array_values(array_unique(array_filter($serviceNames))),
            'analysis' => [
                'summary' => $result['summary'] ?? null,
                'recommendations' => $result['recommendations'],
            ],
            'status' => Lead::STATUS_QUALIFIED,
            'analyzed_at' => now(),
        ]);

        // Save contact details extracted by AI
        if (! $lead->phone && ! empty($result['phone'])) {
            $lead->phone = $result['phone'];
        }
        if (! $lead->email && ! empty($result['email'])) {
            $lead->email = $result['email'];
        }
        if (! $lead->address && ! empty($result['address'])) {
            $lead->address = $result['address'];
        }

        $lead->markScoreClass();
        $lead->save();

        // Save people/contacts from AI analysis
        $contacts = $result['contacts'] ?? [];
        if (! empty($contacts) && is_array($contacts)) {
            foreach ($contacts as $person) {
                if (empty($person['name']) && empty($person['email'])) {
                    continue;
                }
                $exists = \App\Models\LeadContact::where('lead_id', $lead->id)
                    ->where(function ($q) use ($person) {
                        if (! empty($person['email'])) {
                            $q->where('email', $person['email']);
                        } elseif (! empty($person['name'])) {
                            $q->where('name', $person['name']);
                        }
                    })->exists();

                if (! $exists) {
                    \App\Models\LeadContact::create([
                        'lead_id' => $lead->id,
                        'name' => $person['name'] ?? null,
                        'role' => $person['role'] ?? $person['title'] ?? null,
                        'email' => $person['email'] ?? null,
                        'phone' => $person['phone'] ?? null,
                        'is_primary' => ! \App\Models\LeadContact::where('lead_id', $lead->id)->exists(),
                    ]);
                }
            }
        }

        AiUsageLog::create([
            'user_id' => $lead->owner_id,
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'provider' => $result['provider'] ?? 'heuristic',
            'model' => $result['model'] ?? 'rule-based',
            'prompt_name' => 'opportunity_analysis',
            'operation' => 'analyse_lead',
            'status' => 'completed',
            'cached' => 'no',
            'duration_ms' => $durationMs,
            'created_at' => now(),
        ]);
    }
}