<?php

namespace App\Http\Controllers;

use App\Models\FreelancerAccount;
use App\Models\FreelancerBid;
use App\Services\AuditService;
use App\Services\Freelancer\BidPlacementService;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreelancerBidController extends Controller
{
    public function dashboard()
    {
        $accounts = FreelancerAccount::withCount('bids')->orderBy('name')->get();
        $bids = FreelancerBid::query();
        $totalBids = (clone $bids)->count();

        $statusCounts = (clone $bids)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $periodStart = now()->subDays(13)->startOfDay();
        $activityCounts = (clone $bids)
            ->where('created_at', '>=', $periodStart)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $activity = collect();
        foreach (CarbonPeriod::create($periodStart, now()->startOfDay()) as $date) {
            $day = $date->format('Y-m-d');
            $activity->push([
                'label' => $date->format('d M'),
                'total' => (int) ($activityCounts[$day] ?? 0),
            ]);
        }

        $submitted = (int) ($statusCounts['submitted'] ?? 0);
        $awarded = (int) ($statusCounts['awarded'] ?? 0);
        $activeAccounts = $accounts->where('is_active', true)->count();
        $trackedBudget = (float) (clone $bids)->whereIn('status', ['pending', 'submitted', 'awarded'])->sum('budget_max');
        $bidValue = (float) (clone $bids)->whereIn('status', ['submitted', 'awarded'])->sum('bid_amount');

        $stats = [
            'total' => $totalBids,
            'submitted' => $submitted,
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'awarded' => $awarded,
            'failed' => (int) ($statusCounts['failed'] ?? 0),
            'active_accounts' => $activeAccounts,
            'success_rate' => $totalBids ? round((($submitted + $awarded) / $totalBids) * 100) : 0,
            'tracked_budget' => $trackedBudget,
            'bid_value' => $bidValue,
        ];

        $recentBids = FreelancerBid::with('account')->latest()->limit(8)->get();
        $statusChart = collect(['pending', 'submitted', 'awarded', 'failed', 'skipped', 'rejected'])
            ->mapWithKeys(fn (string $status) => [$status => (int) ($statusCounts[$status] ?? 0)]);

        return view('freelancer.dashboard', compact('accounts', 'activity', 'recentBids', 'stats', 'statusChart'));
    }

    public function index(Request $request)
    {
        $query = FreelancerBid::with('account')->latest();

        if ($request->filled('account_id')) {
            $query->where('freelancer_account_id', $request->integer('account_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $bids = $query->paginate(20)->withQueryString();
        $accounts = FreelancerAccount::orderBy('name')->get(['id', 'name']);

        $stats = [
            'submitted' => FreelancerBid::where('status', 'submitted')->count(),
            'pending' => FreelancerBid::where('status', 'pending')->count(),
            'skipped' => FreelancerBid::where('status', 'skipped')->count(),
            'failed' => FreelancerBid::where('status', 'failed')->count(),
        ];

        return view('freelancer.bids.index', compact('bids', 'accounts', 'stats'));
    }

    public function approve(FreelancerBid $bid, BidPlacementService $bidPlacementService)
    {
        if ($bid->status !== 'pending') {
            return back()->with('error', 'Only pending bids can be submitted.');
        }

        $bidPlacementService->submit($bid);
        AuditService::record(Auth::user(), 'freelancer_bid_submitted', 'FreelancerBid', $bid->id);

        return back()->with($bid->status === 'submitted' ? 'success' : 'error', $bid->status === 'submitted'
            ? 'Bid submitted on Freelancer.com.'
            : 'Bid submission failed: '.$bid->error_message);
    }

    public function reject(FreelancerBid $bid)
    {
        $bid->update(['status' => 'skipped', 'error_message' => 'Manually rejected']);
        AuditService::record(Auth::user(), 'freelancer_bid_rejected', 'FreelancerBid', $bid->id);

        return back()->with('success', 'Bid draft discarded.');
    }
}
