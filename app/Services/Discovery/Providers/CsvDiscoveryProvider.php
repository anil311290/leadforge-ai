<?php

namespace App\Services\Discovery\Providers;

use App\Services\Discovery\Contracts\DiscoveryProvider;

class CsvDiscoveryProvider implements DiscoveryProvider
{
    public function name(): string
    {
        return 'csv';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function discover(string $location, array $options = []): array
    {
        $path = $options['path'] ?? null;

        if (! $path || ! is_file($path)) {
            throw new \RuntimeException('CSV file not found.');
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, null, ',', '"', '\\');
        $results = [];

        while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }
            $record = array_combine($headers, $row) ?: [];
            $name = $record['name'] ?? $record['company'] ?? null;
            if (! $name) {
                continue;
            }
            $results[] = [
                'name' => $name,
                'website' => $record['website'] ?? $record['url'] ?? null,
                'industry' => $record['industry'] ?? null,
                'location' => $record['location'] ?? $location,
                'phone' => $record['phone'] ?? null,
                'email' => $record['email'] ?? null,
                'address' => $record['address'] ?? null,
            ];
        }

        fclose($handle);

        return $results;
    }
}