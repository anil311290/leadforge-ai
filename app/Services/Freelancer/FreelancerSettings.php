<?php

namespace App\Services\Freelancer;

use App\Models\Setting;

/**
 * Global Freelancer.com defaults, editable from the UI (Settings table) with
 * config/freelancer.php only used as the initial fallback.
 */
class FreelancerSettings
{
    protected const GROUP = 'freelancer';

    protected const KEYS = [
        'scan_interval_minutes' => 'freelancer.scan_interval_minutes',
        'delay_min_sec' => 'freelancer.delay_min_sec',
        'delay_max_sec' => 'freelancer.delay_max_sec',
        'default_include_keywords' => 'freelancer.default_include_keywords',
        'default_exclude_keywords' => 'freelancer.default_exclude_keywords',
        'default_exclude_countries' => 'freelancer.default_exclude_countries',
        'proposal_use_ai' => 'freelancer.proposal.use_ai',
        'profile_title' => 'freelancer.proposal.profile_title',
        'profile_summary' => 'freelancer.proposal.profile_summary',
        'portfolio_url' => 'freelancer.proposal.portfolio_url',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $configPath = static::KEYS[$key] ?? null;
        $fallback = $default ?? ($configPath ? config($configPath) : null);

        return Setting::get(static::settingKey($key), $fallback);
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::set(static::settingKey($key), $value, self::GROUP);
    }

    public static function all(): array
    {
        $values = [];
        foreach (array_keys(self::KEYS) as $key) {
            $values[$key] = static::get($key);
        }

        return $values;
    }

    protected static function settingKey(string $key): string
    {
        return self::GROUP.'.'.$key;
    }
}
