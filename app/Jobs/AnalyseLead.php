<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\Analysis\AnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyseLead implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $timeout = 180;

    public function __construct(public Lead $lead)
    {
    }

    public function handle(AnalysisService $service): void
    {
        $service->analyseLead($this->lead);
    }
}