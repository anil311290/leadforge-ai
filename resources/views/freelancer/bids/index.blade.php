@extends('layouts.app')
@section('title', 'Freelancer Bid Tracking')

@section('content')
<style>
    .bid-table { font-size:.78rem; }
    .bid-table { min-width:1080px; border-collapse:separate; border-spacing:0; }
    .bid-table th { position:sticky; top:0; z-index:2; padding:.72rem .65rem; color:#64748b; background:#f8fafc; border-bottom:1px solid #dfe7f0; font-size:.66rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
    .bid-table td { padding:.78rem .65rem; border-bottom:1px solid #edf1f5; vertical-align:middle; }
    .bid-table tbody tr { transition:background .15s ease, box-shadow .15s ease; }
    .bid-table tbody tr:nth-child(even) { background:#fbfcfe; }
    .bid-table tbody tr:hover { background:#f0f6ff; box-shadow:inset 3px 0 0 #2f86f6; }
    .bid-table td:first-child { min-width:155px; max-width:190px; }
    .bid-table td:nth-child(2) { max-width:145px; }
    .bid-table td:nth-child(4), .bid-table td:nth-child(5), .bid-table td:nth-child(6), .bid-table td:nth-child(7) { min-width:112px; }
    .bid-table td:nth-child(8) { min-width:85px; }
    .bid-table td:nth-child(9) { min-width:92px; }
    .bid-table td:first-child a { display:inline-block; max-width:175px; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom; white-space:nowrap; font-weight:750; }
    .bid-table .proposal-preview { display:block; max-width:135px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .bid-table .small { font-size:.75rem !important; }
    .bid-table .text-muted { font-size:.64rem !important; }
    .bid-table .badge { padding:.38rem .55rem; font-size:.66rem; font-weight:700; letter-spacing:.01em; }
    .bid-table .view-proposal-btn { color:#286dcc; font-size:.7rem; font-weight:700; text-decoration:none; }
    .bid-table .view-proposal-btn:hover { color:#174f9f; text-decoration:underline; }
    .bid-table-wrap { overflow:auto; border-radius:0 0 .5rem .5rem; scrollbar-color:#b7c6d8 #f1f5f9; scrollbar-width:thin; }
    .bid-table-wrap::-webkit-scrollbar { height:8px; }
    .bid-table-wrap::-webkit-scrollbar-track { background:#f1f5f9; }
    .bid-table-wrap::-webkit-scrollbar-thumb { background:#b7c6d8; border-radius:99px; }
    .table-toolbar { background:#fff; border-bottom:1px solid #e8eef5; }
    .bid-table .sort-link { display:inline-flex; align-items:center; gap:.25rem; color:#64748b; text-decoration:none; }
    .bid-table .sort-link:hover { color:#1d65b5; text-decoration:none; }
    .bid-table .sort-link i { font-size:.65rem; }
    @media (max-width:767px) {
            .bid-table { font-size:.72rem; }
        .bid-table th { padding:.55rem .45rem; font-size:.62rem; }
        .bid-table td { padding:.55rem .45rem; }
        .bid-table .small { font-size:.69rem !important; }
    }
    @media (min-width:768px) {
        .bid-table { min-width:100%; table-layout:fixed; }
        .bid-table th:nth-child(1), .bid-table td:nth-child(1) { width:15%; }
        .bid-table th:nth-child(2), .bid-table td:nth-child(2) { width:12%; }
        .bid-table th:nth-child(3), .bid-table td:nth-child(3) { width:9%; }
        .bid-table th:nth-child(4), .bid-table td:nth-child(4) { width:11%; }
        .bid-table th:nth-child(5), .bid-table td:nth-child(5) { width:9%; }
        .bid-table th:nth-child(6), .bid-table td:nth-child(6) { width:10%; }
        .bid-table th:nth-child(7), .bid-table td:nth-child(7) { width:10%; }
        .bid-table th:nth-child(8), .bid-table td:nth-child(8) { width:8%; }
        .bid-table th:nth-child(9), .bid-table td:nth-child(9) { width:8%; }
        .bid-table th:nth-child(10), .bid-table td:nth-child(10) { width:8%; }
        .bid-table td:first-child, .bid-table td:nth-child(2) { min-width:0; max-width:none; }
        .bid-table td:first-child a, .bid-table .proposal-preview { max-width:100%; }
    }
</style>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div><h4 class="fw-bold mb-1">Bid Tracking</h4><p class="text-muted small mb-0">Every bid attempt across all connected Freelancer.com accounts.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('freelancer.bids.export', request()->except('page')) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#howItWorksModal"><i class="bi bi-question-circle me-1"></i>How auto-bidding works</button></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body py-3"><div class="text-muted small">Submitted</div><div class="fs-4 fw-bold">{{ $stats['submitted'] }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body py-3"><div class="text-muted small">Pending review</div><div class="fs-4 fw-bold">{{ $stats['pending'] }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body py-3"><div class="text-muted small">Skipped</div><div class="fs-4 fw-bold">{{ $stats['skipped'] }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body py-3"><div class="text-muted small">Failed</div><div class="fs-4 fw-bold">{{ $stats['failed'] }}</div></div></div></div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Account</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">All accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['pending','submitted','failed','skipped','awarded','rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') == $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button></div>
            <div class="col-md-2"><a href="{{ route('freelancer.bids.index') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-x-lg me-1"></i>Reset</a></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header table-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
        <form method="GET" action="{{ route('freelancer.bids.index') }}" class="d-flex align-items-center gap-2">
            @if(request('account_id'))<input type="hidden" name="account_id" value="{{ request('account_id') }}">@endif
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            @if(request('direction'))<input type="hidden" name="direction" value="{{ request('direction') }}">@endif
            <label for="table-per-page" class="small fw-semibold text-muted mb-0">Rows per page</label>
            <select id="table-per-page" name="per_page" class="form-select form-select-sm" style="width:92px;" onchange="this.form.submit()">
                @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </form>
        <div class="small text-muted">Click a column heading to sort</div>
    </div>
    <div class="bid-table-wrap">
        <table class="table bid-table align-middle mb-0">
            @php($currentSort = request('sort', 'created'))
            @php($currentDirection = request('direction', 'desc'))
            @php($sortUrl = fn (string $sort) => request()->fullUrlWithQuery(['sort' => $sort, 'direction' => $currentSort === $sort && $currentDirection === 'asc' ? 'desc' : 'asc', 'page' => null]))
            <thead><tr><th><a class="sort-link" href="{{ $sortUrl('project') }}">Project @if($currentSort === 'project')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th>Proposal</th><th><a class="sort-link" href="{{ $sortUrl('account') }}">Account @if($currentSort === 'account')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th><a class="sort-link" href="{{ $sortUrl('budget') }}">Client budget @if($currentSort === 'budget')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th><a class="sort-link" href="{{ $sortUrl('bid') }}">Client bid @if($currentSort === 'bid')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th>Internal costing</th><th>Internal timeline</th><th><a class="sort-link" href="{{ $sortUrl('status') }}">Status @if($currentSort === 'status')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th><a class="sort-link" href="{{ $sortUrl('created') }}">When @if($currentSort === 'created')<i class="bi bi-chevron-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>@endif</a></th><th></th></tr></thead>
            <tbody>
            @forelse($bids as $bid)
                <tr>
                    <td>
                        @if($bid->project_url)<a href="{{ $bid->project_url }}" title="{{ $bid->project_title ?? 'Project #'.$bid->project_id }}" target="_blank" rel="noopener">{{ Str::limit($bid->project_title ?? 'Project #'.$bid->project_id, 32, '...') }}</a>@else{{ Str::limit($bid->project_title ?? 'Project #'.$bid->project_id, 32, '...') }}@endif
                        @if($bid->error_message)<div class="small text-danger">{{ $bid->error_message }}</div>@endif
                    </td>
                    <td style="max-width:260px;">
                        @if($bid->proposal_text)
                            <div class="small text-muted proposal-preview" title="{{ $bid->proposal_text }}">{{ Str::limit($bid->proposal_text, 72, '...') }}</div>
                            <button type="button" class="btn btn-sm btn-link p-0 view-proposal-btn" data-proposal="{{ e($bid->proposal_text) }}" data-project="{{ e($bid->project_title ?? 'Project #'.$bid->project_id) }}">View full proposal</button>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small">{{ $bid->account->name ?? '—' }}</td>
                    <td class="small">{{ $bid->currency_sign }}{{ number_format($bid->budget_min ?? 0) }} – {{ $bid->currency_sign }}{{ number_format($bid->budget_max ?? 0) }} {{ $bid->currency_code }}</td>
                    <td class="small fw-semibold">{{ $bid->bid_amount ? $bid->currency_sign.number_format($bid->bid_amount, 2) : '—' }}<div class="text-muted" style="font-size:10px;">Sent to Freelancer</div></td>
                    <td class="small fw-semibold"><strong>{{ $bid->internal_cost ? $bid->currency_sign.number_format($bid->internal_cost, 2) : '—' }}</strong><div class="text-muted" style="font-size:10px;">Private estimate</div></td>
                    <td><span class="badge rounded-pill text-bg-primary">{{ $bid->internal_timeline_days ? $bid->internal_timeline_days.' days' : '—' }}</span><div class="text-muted mt-1" style="font-size:10px;">Private estimate</div></td>
                    <td>
                        @php($badge = ['submitted'=>'text-bg-success','pending'=>'text-bg-warning','failed'=>'text-bg-danger','skipped'=>'bg-light text-dark border','awarded'=>'text-bg-success','rejected'=>'bg-light text-dark border'][$bid->status] ?? 'bg-light text-dark border')
                        <span class="badge {{ $badge }}">{{ ucfirst($bid->status) }}</span>
                    </td>
                    <td class="small">{{ $bid->created_at->diffForHumans() }}</td>
                    <td class="text-nowrap">
                        @if($bid->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('freelancer.bids.approve', $bid) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Submit bid"><i class="bi bi-send"></i></button>
                                </form>
                                <form action="{{ route('freelancer.bids.reject', $bid) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Discard"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10"><div class="empty-state"><i class="bi bi-send"></i><h6 class="mt-2 fw-bold">No bids yet</h6><p>Connect a Freelancer.com account and run a scan to see bid activity here.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bids->hasPages())<div class="card-footer">{{ $bids->links() }}</div>@endif
</div>

<!-- Full proposal text -->
<div class="modal fade" id="proposalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="proposalModalTitle">Proposal</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><p class="mb-0" style="white-space:pre-wrap;" id="proposalModalBody"></p></div>
        </div>
    </div>
</div>

<!-- How auto-bidding works -->
<div class="modal fade" id="howItWorksModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">How automatic bidding works</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <ol class="mb-3">
                    <li class="mb-2"><strong>Add an account.</strong> Go to <a href="{{ route('freelancer.accounts.index') }}">Freelancer Accounts</a> → Add Account, paste the OAuth token from your Freelancer.com Developer app, and set budget/keyword filters.</li>
                    <li class="mb-2"><strong>Test the connection.</strong> Click the plug icon on the account to confirm the key works.</li>
                    <li class="mb-2"><strong>Scan runs automatically.</strong> A background job scans each active account every <code>{{ \App\Services\Freelancer\FreelancerSettings::get('scan_interval_minutes') }}</code> minutes (configurable in <a href="{{ route('freelancer.settings.index') }}">Freelancer Settings</a>), or click the search icon to scan right now.</li>
                    <li class="mb-2"><strong>Projects are filtered</strong> by include/exclude keywords, minimum budget, and excluded countries — non-matching projects show up here as “Skipped” with the reason.</li>
                    <li class="mb-2"><strong>A proposal is generated</strong> for each eligible project (AI-written if configured, otherwise a template) — click “View full proposal” in the table to read it.</li>
                    <li class="mb-2"><strong>Submission depends on the account's “Auto-submit bids” toggle:</strong>
                        <ul class="mt-1">
                            <li><em>Off (default)</em> — the bid is saved as <span class="badge text-bg-warning">Pending</span>. Review the proposal here and click the green send icon to actually place it on Freelancer.com, or the red icon to discard it.</li>
                            <li><em>On</em> — the bid is submitted to Freelancer.com immediately during the scan, no review needed.</li>
                        </ul>
                    </li>
                    <li>Each account stops bidding once it hits its own daily <code>Max bids / day</code> limit, and any API error (invalid key, rate limit, already bid, etc.) shows a plain-English message instead of a raw error.</li>
                </ol>
                <div class="alert alert-warning small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Turn on auto-submit only after you've reviewed a few pending proposals — it places real bids using real Freelancer.com bid tokens.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.view-proposal-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('proposalModalTitle').textContent = 'Proposal — ' + this.dataset.project;
        document.getElementById('proposalModalBody').textContent = this.dataset.proposal;
        new bootstrap.Modal(document.getElementById('proposalModal')).show();
    });
});
</script>
@endsection
