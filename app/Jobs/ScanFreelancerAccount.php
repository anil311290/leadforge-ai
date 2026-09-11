<?php

namespace App\Jobs;

use App\Models\FreelancerAccount;
use App\Services\Freelancer\BidPlacementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanFreelancerAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $freelancerAccountId)
    {
    }

    public function handle(BidPlacementService $bidPlacementService): void
    {
        $account = FreelancerAccount::find($this->freelancerAccountId);

        if (! $account || ! $account->is_active) {
            return;
        }

        $summary = $bidPlacementService->scanAndBid($account);

        Log::info('Freelancer account scan complete', ['account_id' => $account->id] + $summary);
    }
}
