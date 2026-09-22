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

    public static function normalizeWhatsappPhone(?string $phone, ?string $country = null): ?string
    {
        $digits = self::normalizePhone($phone);
        if (! $digits) {
            return null;
        }

        // A number with an international prefix is already WhatsApp-ready.
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        $countryCode = self::countryCallingCode($country);

        if ($countryCode) {
            $national = ltrim($digits, '0');

            if (str_starts_with($national, $countryCode) && strlen($national) > strlen($countryCode) + 6) {
                return $national;
            }

            return $countryCode.$national;
        }

        // Infer India only when the local number matches India's mobile format.
        $indianMobile = self::normalizeIndianMobile($phone);
        if ($indianMobile) {
            return '91'.$indianMobile;
        }

        // Without a country, do not invent a calling code for unknown numbers.
        return $digits;
    }

    public static function countryCallingCode(?string $country): ?string
    {
        if (! $country) {
            return null;
        }

        $value = strtolower(trim($country));
        $codes = [
            'india' => '91', 'in' => '91', '+91' => '91',
            'united states' => '1', 'united states of america' => '1', 'usa' => '1', 'us' => '1', '+1' => '1',
            'canada' => '1', 'ca' => '1',
            'united kingdom' => '44', 'uk' => '44', 'gb' => '44', '+44' => '44',
            'australia' => '61', 'au' => '61', '+61' => '61',
            'united arab emirates' => '971', 'uae' => '971', 'ae' => '971', '+971' => '971',
            'singapore' => '65', 'sg' => '65', '+65' => '65',
            'germany' => '49', 'de' => '49', '+49' => '49',
        ];

        return $codes[$value] ?? (preg_match('/^\+?(\d{1,3})$/', $value, $match) ? $match[1] : null);
    }
}