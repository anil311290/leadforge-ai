<?php

namespace App\Services\Freelancer;

use App\Models\FreelancerAccount;

/**
 * Decides whether a Freelancer.com project is worth bidding on for a given account,
 * based on that account's keyword/budget/country filters.
 */
class BidEligibilityService
{
    public function isEligible(array $project, FreelancerAccount $account): array
    {
        $title = (string) ($project['title'] ?? '');
        $description = (string) ($project['preview_description'] ?? $project['description'] ?? '');
        $text = mb_strtolower($title.' '.$description);

        $exclude = array_map('mb_strtolower', $account->exclude_keywords ?: (array) FreelancerSettings::get('default_exclude_keywords', []));
        foreach ($exclude as $keyword) {
            if ($keyword !== '' && str_contains($text, $keyword)) {
                return [false, "excluded keyword: {$keyword}"];
            }
        }

        $include = array_map('mb_strtolower', $account->include_keywords ?: (array) FreelancerSettings::get('default_include_keywords', []));
        if (! empty($include)) {
            $matched = false;
            foreach ($include as $keyword) {
                if ($keyword !== '' && str_contains($text, $keyword)) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                return [false, 'no matching skill keywords'];
            }
        }

        [$budgetMin, ] = $this->extractBudget($project);
        if ($account->budget_min > 0 && $budgetMin > 0 && $budgetMin < $account->budget_min) {
            return [false, "budget {$budgetMin} below minimum {$account->budget_min}"];
        }

        $country = $this->extractCountry($project);
        $excludedCountries = array_map('mb_strtoupper', $account->exclude_countries ?: (array) FreelancerSettings::get('default_exclude_countries', []));
        if ($country && in_array($country, $excludedCountries, true)) {
            return [false, "excluded client country: {$country}"];
        }

        return [true, null];
    }

    public function extractBudget(array $project): array
    {
        $budget = $project['budget'] ?? [];
        $min = (float) ($budget['minimum'] ?? 0);
        $max = (float) ($budget['maximum'] ?? $min);

        return [$min, $max];
    }

    public function extractCurrency(array $project): array
    {
        $currency = $project['currency'] ?? [];

        return [
            (string) ($currency['code'] ?? ''),
            (string) ($currency['sign'] ?? ''),
        ];
    }

    public function extractCountry(array $project): ?string
    {
        $loc = $project['from_user_location'] ?? [];

        $code = $loc['country_code'] ?? $loc['code'] ?? null;

        return $code ? mb_strtoupper((string) $code) : null;
    }
}
