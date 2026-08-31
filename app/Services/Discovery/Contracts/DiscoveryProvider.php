<?php

namespace App\Services\Discovery\Contracts;

interface DiscoveryProvider
{
    public function name(): string;

    public function isConfigured(): bool;

    public function discover(string $location, array $options = []): array;
}

