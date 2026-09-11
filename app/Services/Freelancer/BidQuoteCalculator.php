<?php

namespace App\Services\Freelancer;

use App\Models\FreelancerAccount;

class BidQuoteCalculator
{
    /**
     * Return a practical quote from the client's published budget range.
     * The 80% floor prevents bidding at the lower edge of a range, while
     * rounding keeps the amount valid and easy to discuss with the client.
     */
    public function calculate(array $project, float $budgetMin, float $budgetMax, FreelancerAccount $account, string $currencyCode = ''): array
    {
        $defaultAmount = (float) ($account->bid_amount_default ?: 0);
        $hasPublishedBudget = $budgetMin > 0 || $budgetMax > 0;
        $ceiling = $hasPublishedBudget ? max($budgetMax, $budgetMin) : max($defaultAmount, 5);
        $floor = $hasPublishedBudget
            ? max($budgetMin, ($ceiling * 0.80) + 0.01)
            : max($defaultAmount, 5);
        $amount = $this->roundUpToFive($floor);
        $internalCost = $this->roundUpToFive(max($ceiling * 1.20, $amount * 1.25));

        return [
            'amount' => $amount,
            'period_days' => $budgetMin <= 0 && $budgetMax <= 0 && $account->bid_period_days_default
                ? (int) $account->bid_period_days_default
                : $this->estimateTimeline($project, $amount, $currencyCode),
            'internal_cost' => $internalCost,
            'internal_timeline_days' => $this->estimateTimeline($project, $internalCost, $currencyCode),
            'floor' => round($floor, 2),
            'basis' => $budgetMax > 0 ? '80% of published budget ceiling' : 'account default / available project budget',
        ];
    }

    protected function roundUpToFive(float $amount): float
    {
        return (float) (max(1, (int) ceil($amount / 5)) * 5);
    }

    protected function estimateTimeline(array $project, float $amount, string $currencyCode = ''): int
    {
        $text = strtolower((string) (($project['title'] ?? '').' '.($project['description'] ?? '').' '.($project['preview_description'] ?? '')));
        $usdEquivalent = $amount * $this->currencyToUsdRate($currencyCode);
        $days = match (true) {
            $usdEquivalent <= 500 => 5,
            $usdEquivalent <= 2000 => 10,
            $usdEquivalent <= 5000 => 15,
            $usdEquivalent <= 15000 => 20,
            default => 30,
        };

        $complexitySignals = [
            'integration', 'api', 'payment', 'marketplace', 'dashboard', 'admin',
            'automation', 'migration', 'database', 'mobile app', 'ecommerce', 'saas',
        ];
        $signals = collect($complexitySignals)->filter(fn (string $signal) => str_contains($text, $signal))->count();

        if ($signals >= 3) {
            $days += 10;
        } elseif ($signals >= 1) {
            $days += 5;
        }

        return min(45, max(5, (int) (ceil($days / 5) * 5)));
    }

    protected function currencyToUsdRate(string $currencyCode): float
    {
        return match (strtoupper(trim($currencyCode))) {
            'INR' => 0.012,
            'EUR' => 1.08,
            'GBP' => 1.27,
            'AUD' => 0.65,
            'CAD' => 0.73,
            'SGD' => 0.74,
            'AED' => 0.272,
            'NZD' => 0.60,
            default => 1.0,
        };
    }
}
