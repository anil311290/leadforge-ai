<?php

namespace App\Services\Freelancer;

use App\Models\FreelancerAccount;
use Carbon\Carbon;

/**
 * Decides whether a Freelancer.com project is worth bidding on for a given account,
 * based on that account's keyword/budget/country filters.
 */
class BidEligibilityService
{
    public function isEligible(array $project, FreelancerAccount $account): array
    {
        $title = (string) ($project['title'] ?? '');
        $description = (string) ($project['description'] ?? $project['preview_description'] ?? '');
        $jobNames = implode(' ', array_filter(array_map(
            fn ($job) => (string) ($job['name'] ?? ''),
            (array) ($project['jobs'] ?? [])
        )));
        // Include the project's own skill tags, not just the title/description text,
        // so a project genuinely matching the profile's skills isn't skipped just
        // because it doesn't literally repeat the keyword in the free-text fields.
        $text = mb_strtolower($title.' '.$description.' '.$jobNames);

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

        $bidCount = $this->extractBidCount($project);
        if ($account->max_project_bids > 0 && $bidCount > $account->max_project_bids) {
            return [false, "project already has {$bidCount} bids (limit {$account->max_project_bids})"];
        }

        $postedAt = $this->extractPostedAt($project);
        if ($account->max_project_age_hours > 0 && $postedAt?->lt(now('UTC')->subHours($account->max_project_age_hours))) {
            return [false, "project is older than {$account->max_project_age_hours} hours"];
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

    public function extractBidCount(array $project): int
    {
        return max(0, (int) (
            data_get($project, 'bid_stats.bid_count')
            ?? data_get($project, 'bid_count')
            ?? data_get($project, 'bids_count')
            ?? 0
        ));
    }

    public function extractPostedAt(array $project): ?Carbon
    {
        $value = data_get($project, 'time_submitted')
            ?? data_get($project, 'time_created')
            ?? data_get($project, 'time_posted');

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return is_numeric($value)
                ? Carbon::createFromTimestampUTC((int) $value)
                : Carbon::parse((string) $value)->utc();
        } catch (\Throwable) {
            return null;
        }
    }
}
