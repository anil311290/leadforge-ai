<?php
namespace App\Providers;

use App\Models\Lead;
use App\Policies\LeadPolicy;
use App\Services\Discovery\DiscoveryManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DiscoveryManager::class, fn () => new DiscoveryManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DiscoveryManager::boot();
        Gate::policy(Lead::class, LeadPolicy::class);
    }
}
