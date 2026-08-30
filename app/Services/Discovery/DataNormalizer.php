<?php

namespace App\Services\Discovery;

class DataNormalizer
{
    /**
     * Normalize a domain to a canonical form for duplicate detection.
     * Removes scheme, www, path, query, fragment; lowercases.
     */
    public static function normalizeDomain(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);
        if (! str_starts_with($url, 'http') && ! str_starts_with($url, '//')) {
            $url = 'https://'.$url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        return strtolower(preg_replace('/^www\./', '', $host) ?? $host);
    }

    /**
     * Normalize a company name for fuzzy duplicate detection.
     */
    public static function normalizeCompany(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $normalized = strtolower((string) $name);
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b(inc|llc|ltd|pvt|private|limited|llp|corp|corporation|company|co|gmbh)\b/', ' ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);

        return $normalized !== '' ? $normalized : null;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits !== '' ? $digits : null;
    }
}