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

Schedule::call(function () {
    $freelancerScanInterval = max(1, (int) FreelancerSettings::get('scan_interval_minutes', 15));

    if ((int) now()->minute % $freelancerScanInterval !== 0) {
        return;
    }

    FreelancerAccount::where('is_active', true)->pluck('id')->each(
        fn (int $id) => ScanFreelancerAccount::dispatch($id)
    );
})->everyMinute()->name('freelancer-scan')->withoutOverlapping();
