<?php

namespace App\Services\Discovery\Providers;

use App\Services\Discovery\Contracts\DiscoveryProvider;

class SearchApiDiscoveryProvider implements DiscoveryProvider
{
    public function name(): string
    {
        return 'search_api';
    }

    public function isConfigured(): bool
    {
        $config = config('leadforge.discovery.search_api');

        return ! empty($config['endpoint']) && ! empty($config['api_key']);
    }

    public function discover(string $location, array $options = []): array
    {
        throw new \RuntimeException('Search API provider is not configured for automatic discovery.');
    }
}
