<?php

namespace App\Services\Discovery\Providers;

use App\Services\Discovery\Contracts\DiscoveryProvider;

/**
 * Uses user-provided business names/URLs as a compliant discovery source.
 * These are business records manually supplied by the operator.
 */
class ManualUrlDiscoveryProvider implements DiscoveryProvider
{
    public function name(): string
    {
        return 'manual_urls';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function discover(string $location, array $options = []): array
    {
        $input = $options['businesses'] ?? [];

        $results = [];
        foreach ($input as $row) {
            if (is_string($row)) {
                $row = ['name' => $row];
            }
            $name = $row['name'] ?? null;
            if (! $name) {
                continue;
            }
            $results[] = [
                'name' => $name,
                'website' => $row['website'] ?? null,
                'location' => $row['location'] ?? $location,
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'address' => $row['address'] ?? null,
            ];
        }

        return $results;
    }
}