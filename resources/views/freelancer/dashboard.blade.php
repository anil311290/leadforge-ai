@extends('layouts.app')
@section('title', 'Freelancer Overview')

@section('content')
<style>
    .freelancer-hero { background:linear-gradient(135deg,#0e1e3a 0%,#17365f 100%); color:#fff; border-radius:1rem; overflow:hidden; position:relative; }
    .freelancer-hero:after { content:""; position:absolute; width:240px; height:240px; right:-65px; top:-100px; border:1px solid rgba(255,255,255,.13); border-radius:50%; box-shadow:0 0 0 30px rgba(94,162,255,.08),0 0 0 60px rgba(94,162,255,.05); }
    .freelancer-hero .eyebrow { color:#9ec5ff; font-size:.7rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
    .freelancer-hero h1 { font-size:clamp(1.5rem,3vw,2.25rem); letter-spacing:-.045em; }
    .freelancer-hero p { color:#b9cbe2; max-width:570px; }
    .dashboard-card { border:1px solid #e3e9f0; border-radius:.85rem; box-shadow:0 .25rem .9rem rgba(20,42,75,.05); }
    .dashboard-card .card-header { background:transparent; border-bottom:1px solid #edf1f5; }
    .metric-card { min-height:126px; position:relative; overflow:hidden; }
    .metric-card:after { content:""; position:absolute; width:70px; height:70px; right:-23px; bottom:-30px; background:rgba(94,162,255,.1); border-radius:50%; }
    .metric-label { color:#77859a; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .metric-value { color:#172b48; font-size:1.8rem; font-weight:800; letter-spacing:-.06em; }
    .metric-help { color:#8b98a9; font-size:.75rem; }
    .metric-icon { width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:.65rem; font-size:1.05rem; }
    .chart-wrap { height:240px; }
    .status-legend { display:grid; grid-template-columns:repeat(3,1fr); gap:.65rem 1.25rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #edf1f5; }
    .status-legend-item { display:flex; justify-content:space-between; align-items:center; gap:.5rem; color:#66758a; font-size:.76rem; white-space:nowrap; }
    .status-dot { width:8px; height:8px; display:inline-block; margin-right:.4rem; border-radius:50%; }
    .account-progress { height:7px; background:#e8edf4; }
    .account-progress .progress-bar { background:#2f86f6; }
    .account-meta { color:#8190a3; font-size:.75rem; }
    .dashboard-link { display:inline-flex; align-items:center; gap:.35rem; color:#286dcc; font-size:.78rem; font-weight:700; text-decoration:none; white-space:nowrap; }
    .dashboard-link:hover { color:#174f9f; text-decoration:none; }
    .dashboard-link i { font-size:.8rem; transition:transform .18s ease; }
    .dashboard-link:hover i { transform:translate(2px,-1px); }
    .project-link { color:#1b5fba; font-weight:700; text-decoration:none; }
    .project-link:hover { color:#123f7d; text-decoration:underline; text-underline-offset:3px; }
    .filter-bar { border:1px solid #dfe7f0; border-radius:.85rem; background:#fff; box-shadow:0 .25rem .9rem rgba(20,42,75,.04); }
    .filter-bar label { color:#63738a; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .activity-table td, .activity-table th { white-space:nowrap; }
    .activity-table td:first-child { white-space:normal; min-width:210px; }
    .status-badge { font-size:.7rem; letter-spacing:.02em; }
    @media (max-width:767px) { .freelancer-hero .btn { width:100%; } .chart-wrap { height:210px; } .status-legend { grid-template-columns:repeat(2,1fr); gap:.6rem .8rem; } }
</style>

<div class="freelancer-hero p-4 p-md-5 mb-4">
    <div class="position-relative" style="z-index:1;">
        <div class="eyebrow mb-2"><i class="bi bi-bar-chart-line me-1"></i> Freelancer command center</div>
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <h1 class="fw-bold mb-2">Your bidding performance, at a glance.</h1>
                <p class="mb-0">Track account health, scan activity, bid progress and project value from one focused workspace.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('freelancer.bids.index', $selectedAccountId ? ['account_id' => $selectedAccountId] : []) }}" class="btn btn-light btn-sm"><i class="bi bi-send me-1"></i> Review bids</a>
                <a href="{{ route('freelancer.accounts.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-person-badge me-1"></i> Accounts</a>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('freelancer.dashboard') }}" class="filter-bar p-3 mb-4">
    <div class="row align-items-end g-2">
        <div class="col-12 col-md-5 col-lg-4">
            <label for="account_id" class="form-label mb-1">Account filter</label>
            <select id="account_id" name="account_id" class="form-select form-select-sm">
                <option value="">All Freelancer accounts</option>
                @foreach($filterAccounts as $filterAccount)
                    <option value="{{ $filterAccount->id }}" @selected($selectedAccountId === $filterAccount->id)>{{ $filterAccount->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-auto"><button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Apply filter</button></div>
        @if($selectedAccountId)
            <div class="col-12 col-md-auto"><a href="{{ route('freelancer.dashboard') }}" class="btn btn-light btn-sm"><i class="bi bi-x-lg me-1"></i>Clear</a></div>
            <div class="col-12 col-md-auto"><span class="text-muted small">Showing metrics for <strong>{{ $filterAccounts->firstWhere('id', $selectedAccountId)->name }}</strong></span></div>
        @else
            <div class="col-12 col-md-auto"><span class="text-muted small">Showing combined performance across all accounts</span></div>
        @endif
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card dashboard-card metric-card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><div><div class="metric-label">Total bids</div><div class="metric-value mt-1">{{ number_format($stats['total']) }}</div><div class="metric-help">All tracked attempts</div></div><div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-send"></i></div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card dashboard-card metric-card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><div><div class="metric-label">Submitted</div><div class="metric-value mt-1">{{ number_format($stats['submitted']) }}</div><div class="metric-help">{{ $stats['success_rate'] }}% action rate</div></div><div class="metric-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle"></i></div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card dashboard-card metric-card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><div><div class="metric-label">Pending review</div><div class="metric-value mt-1">{{ number_format($stats['pending']) }}</div><div class="metric-help">Needs your decision</div></div><div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card dashboard-card metric-card h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><div><div class="metric-label">Active accounts</div><div class="metric-value mt-1">{{ number_format($stats['active_accounts']) }}</div><div class="metric-help">{{ $stats['awarded'] }} awarded projects</div></div><div class="metric-icon bg-info bg-opacity-10 text-info"><i class="bi bi-person-check"></i></div></div></div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8"><div class="card dashboard-card h-100"><div class="card-header p-3 d-flex justify-content-between align-items-center"><div><h6 class="fw-bold mb-1">Bid activity</h6><div class="text-muted small">New bids created over the last 14 days</div></div><span class="badge bg-light text-dark border"><i class="bi bi-calendar3 me-1"></i>14 days</span></div><div class="card-body"><div class="chart-wrap"><canvas id="bidActivityChart"></canvas></div></div></div></div>
    <div class="col-12 col-xl-4"><div class="card dashboard-card h-100"><div class="card-header p-3"><h6 class="fw-bold mb-1">Bid status mix</h6><div class="text-muted small">How your attempts are moving</div></div><div class="card-body"><div style="height:170px; max-width:245px; margin:0 auto;"><canvas id="bidStatusChart"></canvas></div><div class="status-legend">@foreach($statusChart as $status => $total)<div class="status-legend-item"><span><i class="status-dot" style="background:{{ ['pending'=>'#f5b942','submitted'=>'#20a36a','awarded'=>'#2f86f6','failed'=>'#e05260','skipped'=>'#99a6b7','rejected'=>'#69778b'][$status] }}"></i>{{ ucfirst($status) }}</span><strong>{{ $total }}</strong></div>@endforeach</div></div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7"><div class="card dashboard-card h-100"><div class="card-header p-3 d-flex justify-content-between align-items-center"><div><h6 class="fw-bold mb-1">Account health & daily capacity</h6><div class="text-muted small">Today’s submitted and pending bids against each account limit</div></div><a href="{{ route('freelancer.accounts.index') }}" class="dashboard-link">Manage accounts <i class="bi bi-arrow-up-right"></i></a></div><div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-3">Account</th><th>Status</th><th>Today</th><th class="pe-3" style="min-width:150px;">Capacity</th></tr></thead><tbody>@forelse($accounts as $account)@php($today = $account->todayBidCount()) @php($limit = max((int) $account->max_bids_per_day, 1)) @php($percent = min(round(($today / $limit) * 100), 100))<tr><td class="ps-3"><div class="fw-semibold">{{ $account->name }}</div><div class="account-meta">{{ $account->freelancer_username ?? 'Username not set' }}</div></td><td>@if($account->status === 'connected')<span class="badge text-bg-success status-badge">Connected</span>@elseif($account->status === 'error')<span class="badge text-bg-danger status-badge">Error</span>@else<span class="badge bg-light text-dark border status-badge">Pending</span>@endif</td><td class="small fw-semibold">{{ $today }} / {{ $limit }}</td><td class="pe-3"><div class="progress account-progress mb-1"><div class="progress-bar" style="width:{{ $percent }}%"></div></div><div class="account-meta">{{ $percent }}% used · {{ $account->bids_count }} total</div></td></tr>@empty<tr><td colspan="4"><div class="empty-state py-4"><i class="bi bi-person-badge"></i><p class="mb-0 mt-2">No Freelancer accounts connected yet.</p><a href="{{ route('freelancer.accounts.create') }}" class="btn btn-sm btn-primary mt-2">Add your first account</a></div></td></tr>@endforelse</tbody></table></div></div></div></div>
    <div class="col-12 col-xl-5"><div class="card dashboard-card h-100"><div class="card-header p-3"><h6 class="fw-bold mb-1">Value snapshot</h6><div class="text-muted small">Financial context from tracked projects</div></div><div class="card-body"><div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3"><div><div class="text-muted small">Tracked project budgets</div><div class="fs-4 fw-bold text-dark">{{ number_format($stats['tracked_budget'], 0) }}</div></div><i class="bi bi-wallet2 fs-3 text-primary"></i></div><div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3"><div><div class="text-muted small">Submitted bid value</div><div class="fs-4 fw-bold text-dark">{{ number_format($stats['bid_value'], 0) }}</div></div><i class="bi bi-cash-coin fs-3 text-success"></i></div><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Awarded projects</div><div class="fs-4 fw-bold text-dark">{{ number_format($stats['awarded']) }}</div></div><i class="bi bi-trophy fs-3 text-warning"></i></div></div></div></div>
</div>

<div class="card dashboard-card"><div class="card-header p-3 d-flex justify-content-between align-items-center"><div><h6 class="fw-bold mb-1">Recent bid activity</h6><div class="text-muted small">Latest projects discovered and processed by your accounts</div></div><a href="{{ route('freelancer.bids.index', $selectedAccountId ? ['account_id' => $selectedAccountId] : []) }}" class="dashboard-link">View all <i class="bi bi-arrow-right"></i></a></div><div class="table-responsive"><table class="table activity-table align-middle mb-0"><thead><tr><th class="ps-3">Project</th><th>Account</th><th>Budget</th><th>Bid amount</th><th>Status</th><th class="pe-3">Created</th></tr></thead><tbody>@forelse($recentBids as $bid)<tr><td class="ps-3"><div class="fw-semibold">@if($bid->project_url)<a class="project-link" href="{{ $bid->project_url }}" target="_blank" rel="noopener">{{ $bid->project_title ?? 'Project #'.$bid->project_id }} <i class="bi bi-box-arrow-up-right small"></i></a>@else{{ $bid->project_title ?? 'Project #'.$bid->project_id }}@endif</div><div class="account-meta">{{ $bid->client_country ?? 'Country not listed' }} · {{ $bid->bid_period_days ?? '—' }} day proposal</div></td><td class="small">{{ $bid->account->name ?? '—' }}</td><td class="small">{{ $bid->currency_sign }}{{ number_format($bid->budget_min ?? 0) }} – {{ $bid->currency_sign }}{{ number_format($bid->budget_max ?? 0) }}</td><td class="small fw-semibold">{{ $bid->bid_amount ? $bid->currency_sign.number_format($bid->bid_amount, 2) : '—' }}</td><td><span class="badge {{ ['submitted'=>'text-bg-success','awarded'=>'text-bg-primary','pending'=>'text-bg-warning','failed'=>'text-bg-danger','skipped'=>'bg-light text-dark border','rejected'=>'bg-light text-dark border'][$bid->status] ?? 'bg-light text-dark border' }} status-badge">{{ ucfirst($bid->status) }}</span></td><td class="pe-3 small text-muted">{{ $bid->created_at?->diffForHumans() }}</td></tr>@empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-send"></i><h6 class="mt-2 fw-bold">No bid activity yet</h6><p>Connect a Freelancer account and run a scan to start tracking performance.</p></div></td></tr>@endforelse</tbody></table></div></div>
@endsection

@section('scripts')
<script>
const activity = @json($activity);
const statusMix = @json($statusChart);
new Chart(document.getElementById('bidActivityChart'), { type: 'line', data: { labels: activity.map(item => item.label), datasets: [{ label: 'Bids', data: activity.map(item => item.total), borderColor: '#2f86f6', backgroundColor: 'rgba(47,134,246,.12)', fill: true, tension: .35, pointRadius: 3, pointBackgroundColor: '#fff', pointBorderWidth: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } } });
new Chart(document.getElementById('bidStatusChart'), { type: 'doughnut', data: { labels: Object.keys(statusMix), datasets: [{ data: Object.values(statusMix), backgroundColor: ['#f5b942','#20a36a','#2f86f6','#e05260','#99a6b7','#69778b'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } } });
</script>
@endsection
