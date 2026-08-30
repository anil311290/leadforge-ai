<?php

namespace App\Services\Discovery;

use App\Services\Discovery\Contracts\DiscoveryProvider;

class DiscoveryManager
{
    protected array $registry = [];

    public function register(string $name, string $class): void
    {
        $this->registry[$name] = $class;
    }

    public function make(string $name): DiscoveryProvider
    {
        if (! isset($this->registry[$name])) {
            throw new \RuntimeException("Unknown discovery provider [{$name}].");
        }

        return app($this->registry[$name]);
    }

    /**
     * @return DiscoveryProvider[]
     */
    public function getProviders(): array
    {
        $providers = [];
        $enabled = config('leadforge.discovery.enabled_providers', []);

        foreach ($enabled as $name) {
            $providers[] = $this->make($name);
        }

        return $providers;
    }

    public static function boot(): void
    {
        $manager = app(self::class);

        foreach (config('leadforge.discovery.providers', []) as $name => $class) {
            $manager->register($name, $class);
        }
    }
}