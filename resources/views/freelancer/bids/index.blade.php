@extends('layouts.app')
@section('title', 'Freelancer Bid Tracking')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div><h4 class="fw-bold mb-1">Bid Tracking</h4><p class="text-muted small mb-0">Every bid attempt across all connected Freelancer.com accounts.</p></div>
    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#howItWorksModal"><i class="bi bi-question-circle me-1"></i>How auto-bidding works</button>
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
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Account</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">All accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['pending','submitted','failed','skipped','awarded','rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') == $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filter</button></div>
            <div class="col-md-2"><a href="{{ route('freelancer.bids.index') }}" class="btn btn-sm btn-light w-100">Reset</a></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Project</th><th>Proposal</th><th>Account</th><th>Budget</th><th>Bid</th><th>Status</th><th>When</th><th></th></tr></thead>
            <tbody>
            @forelse($bids as $bid)
                <tr>
                    <td>
                        @if($bid->project_url)<a href="{{ $bid->project_url }}" target="_blank" rel="noopener">{{ $bid->project_title ?? 'Project #'.$bid->project_id }}</a>@else{{ $bid->project_title ?? 'Project #'.$bid->project_id }}@endif
                        @if($bid->error_message)<div class="small text-danger">{{ $bid->error_message }}</div>@endif
                    </td>
                    <td style="max-width:260px;">
                        @if($bid->proposal_text)
                            <div class="small text-muted text-truncate" style="max-width:240px;">{{ $bid->proposal_text }}</div>
                            <button type="button" class="btn btn-sm btn-link p-0 view-proposal-btn" data-proposal="{{ e($bid->proposal_text) }}" data-project="{{ e($bid->project_title ?? 'Project #'.$bid->project_id) }}">View full proposal</button>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small">{{ $bid->account->name ?? '—' }}</td>
                    <td class="small">{{ $bid->currency_sign }}{{ number_format($bid->budget_min ?? 0) }} – {{ $bid->currency_sign }}{{ number_format($bid->budget_max ?? 0) }} {{ $bid->currency_code }}</td>
                    <td class="small">{{ $bid->bid_amount ? $bid->currency_sign.number_format($bid->bid_amount, 2) : '—' }}</td>
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
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-send"></i><h6 class="mt-2 fw-bold">No bids yet</h6><p>Connect a Freelancer.com account and run a scan to see bid activity here.</p></div></td></tr>
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
