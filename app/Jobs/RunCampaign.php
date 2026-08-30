<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Discovery\DiscoveryEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunCampaign implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    public $timeout = 300;

    public function __construct(public Campaign $campaign)
    {
    }

    public function handle(DiscoveryEngine $engine): void
    {
        if ($this->campaign->status === 'cancelled') {
            return;
        }

        try {
            $engine->runForCampaign($this->campaign);
        } catch (\Throwable $e) {
            Log::error('Campaign run failed', ['campaign' => $this->campaign->id, 'error' => $e->getMessage()]);
            $this->campaign->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}