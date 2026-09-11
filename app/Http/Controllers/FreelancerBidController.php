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
    public function dashboard(Request $request)
    {
        $filterAccounts = FreelancerAccount::orderBy('name')->get(['id', 'name']);
        $selectedAccountId = $request->filled('account_id') ? $request->integer('account_id') : null;
        $accounts = FreelancerAccount::withCount('bids')->orderBy('name')->get();
        if ($selectedAccountId && $filterAccounts->contains('id', $selectedAccountId)) {
            $accounts = $accounts->where('id', $selectedAccountId)->values();
        } else {
            $selectedAccountId = null;
        }

        $bids = FreelancerBid::query();
        if ($selectedAccountId) {
            $bids->where('freelancer_account_id', $selectedAccountId);
        }
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

        $recentBids = (clone $bids)->with('account')->latest()->limit(8)->get();
        $statusChart = collect(['pending', 'submitted', 'awarded', 'failed', 'skipped', 'rejected'])
            ->mapWithKeys(fn (string $status) => [$status => (int) ($statusCounts[$status] ?? 0)]);

        return view('freelancer.dashboard', compact('accounts', 'activity', 'recentBids', 'stats', 'statusChart', 'filterAccounts', 'selectedAccountId'));
    }

    public function index(Request $request)
    {
        $perPage = $this->perPage($request);
        $bids = $this->filteredBids($request)->paginate($perPage)->withQueryString();
        $accounts = FreelancerAccount::orderBy('name')->get(['id', 'name']);

        $stats = [
            'submitted' => FreelancerBid::where('status', 'submitted')->count(),
            'pending' => FreelancerBid::where('status', 'pending')->count(),
            'skipped' => FreelancerBid::where('status', 'skipped')->count(),
            'failed' => FreelancerBid::where('status', 'failed')->count(),
        ];

        return view('freelancer.bids.index', compact('bids', 'accounts', 'stats'));
    }

    public function export(Request $request)
    {
        $bids = $this->filteredBids($request)->get();
        $filename = 'freelancer-bids-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($bids) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Project', 'Account', 'Client budget', 'Client bid', 'Client timeline', 'Internal costing', 'Internal timeline', 'Status', 'Country', 'Created']);

            foreach ($bids as $bid) {
                fputcsv($output, [
                    $bid->project_title ?? 'Project #'.$bid->project_id,
                    $bid->account->name ?? '',
                    trim(($bid->currency_sign ?? '').number_format($bid->budget_min ?? 0).' - '.($bid->currency_sign ?? '').number_format($bid->budget_max ?? 0).' '.($bid->currency_code ?? '')),
                    $bid->bid_amount ? ($bid->currency_sign ?? '').number_format($bid->bid_amount, 2) : '',
                    $bid->bid_period_days ? $bid->bid_period_days.' days' : '',
                    $bid->internal_cost ? ($bid->currency_sign ?? '').number_format($bid->internal_cost, 2) : '',
                    $bid->internal_timeline_days ? $bid->internal_timeline_days.' days' : '',
                    ucfirst((string) $bid->status),
                    $bid->client_country ?? '',
                    optional($bid->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function filteredBids(Request $request)
    {
        $sorts = [
            'project' => 'project_title',
            'account' => 'freelancer_account_id',
            'budget' => 'budget_max',
            'bid' => 'bid_amount',
            'status' => 'status',
            'created' => 'created_at',
        ];
        $sort = $sorts[$request->string('sort')->toString()] ?? 'created_at';
        $direction = strtolower($request->string('direction')->toString()) === 'asc' ? 'asc' : 'desc';

        return FreelancerBid::with('account')
            ->when($request->filled('account_id'), fn ($query) => $query->where('freelancer_account_id', $request->integer('account_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy($sort, $direction);
    }

    protected function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 20);

        return in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
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
