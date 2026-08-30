<?php

namespace App\Services\Analysis;

use App\Models\Service;

class ProjectValueEstimator
{
    /**
     * Estimate a project value range for a service given signals/company size.
     *
     * @param  array  $metrics e.g. score, digital_maturity
     * @return array{min:int, max:int}
     */
    public function estimate(Service $service, array $metrics = []): array
    {
        $min = (int) $service->min_value;
        $max = (int) $service->max_value;

        // Use configured defaults if not set on the service record
        if ($min === 0 && $max === 0) {
            [$min, $max] = config('leadforge.project_values.'.$service->name, [50000, 200000]);
        }

        // Scale by company potential / digital maturity if known
        $maturity = $metrics['digital_maturity'] ?? 0;
        $potential = $metrics['company_potential'] ?? 50;

        $factor = 1 + (($maturity / 100) * 0.25) + ((($potential - 50) / 50) * 0.25);
        $factor = max(0.6, min(1.8, $factor));

        return [
            'min' => (int) round($min * $factor / 1000) * 1000,
            'max' => (int) round($max * $factor / 1000) * 1000,
        ];
    }
}