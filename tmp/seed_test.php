<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Lead;
use App\Models\FollowUp;

$user = User::first() ?? User::factory()->create();

// seed leads
for ($i = 1; $i <= 5; $i++) {
    if (Lead::where('company', 'Acme Corp '.$i)->exists()) continue;
    $lead = Lead::create([
        'company' => 'Acme Corp '.$i,
        'normalized_company' => 'acme-corp-'.$i,
        'website' => "https://acme$i.com",
        'normalized_domain' => "acme$i.com",
        'industry' => 'Software',
        'country' => 'US',
        'email' => "contact$i@acme$i.com",
        'source' => 'manual',
        'opportunity_score' => 80 + $i,
        'score_class' => 'HIGH',
        'status' => Lead::STATUS_NEW,
        'owner_id' => $user->id,
    ]);
    $lead->markScoreClass();

    // followups: pending overdue, pending scheduled, sent
    FollowUp::create([
        'lead_id' => $lead->id,
        'sequence_number' => 1,
        'scheduled_at' => now()->subDay(),
        'content' => 'This is overdue #1',
        'status' => 'pending',
    ]);
    FollowUp::create([
        'lead_id' => $lead->id,
        'sequence_number' => 2,
        'scheduled_at' => now()->addDays(1),
        'content' => 'This is scheduled #2',
        'status' => 'pending',
    ]);
    FollowUp::create([
        'lead_id' => $lead->id,
        'sequence_number' => 3,
        'scheduled_at' => now()->subDays(2),
        'content' => 'This was sent #3',
        'status' => 'sent',
        'sent_at' => now()->subDays(2),
    ]);
}

echo "Users: ".User::count().PHP_EOL;
echo "Leads: ".Lead::count().PHP_EOL;
echo "FollowUps: ".FollowUp::count().PHP_EOL;
echo "Pending overdue: ".FollowUp::where('status','pending')->where('scheduled_at','<=',now())->count().PHP_EOL;
echo "Pending scheduled: ".FollowUp::where('status','pending')->where('scheduled_at','>',now())->count().PHP_EOL;
echo "Sent: ".FollowUp::where('status','sent')->count().PHP_EOL;