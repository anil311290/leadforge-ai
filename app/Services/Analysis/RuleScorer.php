<?php

namespace App\Services\Analysis;

use App\Models\Service;
use App\Models\WebsiteScan;
use App\Models\WebsiteScanSignal;

/**
 * Deterministic rule-based opportunity scoring used to sanity-check and
 * enrich AI output, and as a real fallback when no AI is configured.
 */
class RuleScorer
{
    public function score(WebsiteScan $scan): array
    {
        $signals = $scan->signals()->get();

        $opportunities = [];
        $services = Service::where('is_active', true)->get();

        foreach ($services as $service) {
            $opportunities[] = $this->scoreService($service, $signals);
        }

        usort($opportunities, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $opportunities;
    }

    protected function scoreService(Service $service, $signals): array
    {
        $positiveWeight = 0;
        $negativeWeight = 0;
        $evidence = [];

        foreach ($service->rules as $rule) {
            if ($rule->type === 'required_signal' && ! $this->signalMatches($rule, $signals)) {
                return $this->empty($service, 'required signal missing');
            }
        }

        foreach ($service->rules as $rule) {
            if ($rule->type === 'positive_signal' && $this->signalMatches($rule, $signals)) {
                $w = $this->weight($rule);
                $positiveWeight += $w;
                $evidence[] = ['signal' => $rule->signal ?? $rule->keyword, 'weight' => $w];
            }
            if ($rule->type === 'negative_signal' && $this->signalMatches($rule, $signals)) {
                $negativeWeight += $this->weight($rule);
            }
        }

        $score = max(0, min(100, $positiveWeight - $negativeWeight));

        return [
            'service_name' => $service->name,
            'score' => $score,
            'confidence' => $positiveWeight > 0 ? min(95, 40 + $positiveWeight) : 0,
            'evidence' => $evidence,
            'status' => $score >= 60 ? 'recommended' : 'potential',
        ];
    }

    protected function signalMatches($rule, $signals): bool
    {
        $keyword = strtolower((string) ($rule->keyword ?? $rule->signal ?? ''));

        if (! $keyword) {
            return false;
        }

        // Search in the predefined signal set gathered by the crawler
        foreach ($signals as $signal) {
            $text = strtolower((string) $signal->signal.' '.($signal->detail ?? ''));
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function weight($rule): int
    {
        return max(1, min(50, (int) ($rule->weight ?? 20)));
    }

    protected function empty($serviceOrRule, string $reason): array
    {
        return [
            'service_name' => ($serviceOrRule->service ?? null) ? $serviceOrRule->service->name : $serviceOrRule->name,
            'score' => 0,
            'confidence' => 0,
            'evidence' => [],
            'inferred' => $reason,
        ];
    }
}