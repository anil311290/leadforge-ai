<?php

use App\Jobs\ScanFreelancerAccount;
use App\Models\FreelancerAccount;
use App\Services\Freelancer\FreelancerSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Interval is read fresh on every schedule:run tick, so the UI setting takes effect without a deploy.
$freelancerScanInterval = max(1, (int) FreelancerSettings::get('scan_interval_minutes', 15));

Schedule::call(function () {
    FreelancerAccount::where('is_active', true)->pluck('id')->each(
        fn (int $id) => ScanFreelancerAccount::dispatch($id)
    );
})->cron("*/{$freelancerScanInterval} * * * *")->name('freelancer-scan')->withoutOverlapping();
