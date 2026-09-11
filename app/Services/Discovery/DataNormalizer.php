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

    public static function normalizeIndianMobile(?string $phone): ?string
    {
        $digits = self::normalizePhone($phone);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) !== 10 || ! preg_match('/^[6-9]\d{9}$/', $digits)) {
            return null;
        }

        if (preg_match('/^(\d)\1{9}$/', $digits) || in_array($digits, ['1234567890', '9876543210'], true)) {
            return null;
        }

        return $digits;
    }

    public static function normalizeWhatsappPhone(?string $phone, string $countryCode = '91'): ?string
    {
        $mobile = self::normalizeIndianMobile($phone);
        if ($mobile) {
            return $countryCode.$mobile;
        }

        $digits = self::normalizePhone($phone);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0'.$countryCode) && strlen($digits) === strlen($countryCode) + 11) {
            return substr($digits, 1);
        }

        if (str_starts_with($digits, $countryCode) && strlen($digits) === strlen($countryCode) + 10) {
            return $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return $countryCode.substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return $countryCode.$digits;
        }

        return $digits;
    }
}