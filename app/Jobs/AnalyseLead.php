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

        $campaign = $this->lead->campaign;
        if ($campaign && in_array($campaign->status, ['running', 'paused'])) {
            $total = max($campaign->leads()->count(), 1);
            $analysed = $campaign->leads()->whereNotNull('analyzed_at')->count();

            \App\Models\Campaign::where('id', $campaign->id)->update([
                'progress' => min(85 + (int) round(($analysed / $total) * 14), 99),
                'progress_message' => "Analysing opportunities: {$analysed}/{$total} leads scored",
            ]);
        }
    }
}