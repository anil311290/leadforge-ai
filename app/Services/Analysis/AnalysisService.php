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

        if (! $scan) {
            $lead->update(['status' => Lead::STATUS_ANALYZED]);
            $this->activities->log($lead->owner, 'analysis_completed', 'Lead', $lead->id, 'No website to analyse for '.$lead->company);

            return;
        }

        $start = microtime(true);

        try {
            $result = $this->ai->isConfigured()
                ? $this->runAiAnalysis($lead)
                : $this->runHeuristicAnalysis($lead);

            if (empty($result['recommendations']) && $this->ai->isConfigured()) {
                $result['recommendations'] = $this->runHeuristicAnalysis($lead)['recommendations'];
            }

            $this->persist($lead, $scan, $result, round((microtime(true) - $start) * 1000));
            $this->activities->log($lead->owner, 'analysis_completed', 'Lead', $lead->id, 'Analysis completed for '.$lead->company);
        } catch (\Throwable $e) {
            Log::error('Lead analysis failed', ['lead' => $lead->id, 'error' => $e->getMessage()]);
            $lead->update(['status' => Lead::STATUS_ANALYZED, 'analysis' => ['error' => $e->getMessage()]]);
        }
    }

    protected function runAiAnalysis(Lead $lead): array
    {
        $built = $this->prompts->build($lead->latestScan);

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

    protected function runHeuristicAnalysis(Lead $lead): array
    {
        $scan = $lead->latestScan;
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

    protected function persist(Lead $lead, WebsiteScan $scan, array $result, int $durationMs): void
    {
        $top = $result['recommendations'][0] ?? null;
        $leadScore = $result['score'] ?? ($top['score'] ?? 0);

        $analysis = AiAnalysis::create([
            'lead_id' => $lead->id,
            'scan_id' => $scan->id,
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
        $lead->markScoreClass();
        $lead->save();

        AiUsageLog::create([
            'user_id' => $lead->owner_id,
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'provider' => $result['provider'],
            'model' => $result['model'],
            'prompt_name' => 'opportunity_analysis',
            'operation' => 'analyse_lead',
            'status' => 'completed',
            'cached' => 'no',
            'duration_ms' => $durationMs,
            'created_at' => now(),
        ]);
    }
}