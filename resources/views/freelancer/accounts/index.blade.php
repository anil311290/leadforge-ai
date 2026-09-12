@extends('layouts.app')
@section('title', 'Freelancer Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Freelancer.com Accounts</h4><p class="text-muted small mb-0">Connect multiple Freelancer.com accounts, each with its own API key and bidding rules.</p></div>
    <a href="{{ route('freelancer.accounts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Account</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Account</th><th>Status</th><th>Auto-submit</th><th>Daily limit</th><th>Bids placed</th><th>Last scanned</th><th></th></tr></thead>
            <tbody>
            @forelse($accounts as $account)
                <tr>
                    <td>
                        <a href="{{ route('freelancer.accounts.edit', $account) }}" class="fw-semibold">{{ $account->name }}</a>
                        <div class="small text-muted">{{ $account->freelancer_username ?? '—' }}</div>
                    </td>
                    <td>
                        @if($account->status === 'connected')<span class="badge text-bg-success">Connected</span>
                        @elseif($account->status === 'error')<span class="badge text-bg-danger" title="{{ $account->last_error }}">Error</span>
                        @else<span class="badge bg-light text-dark border">Pending</span>@endif
                        @if(!$account->is_active)<span class="badge bg-light text-dark border ms-1">Inactive</span>@endif
                    </td>
                    <td class="small">{{ $account->auto_submit_bids ? 'Yes' : 'Draft only' }}</td>
                    <td class="small">{{ $account->max_bids_per_day }}/day</td>
                    <td class="small">
                        <a href="{{ route('freelancer.bids.index', ['account_id' => $account->id]) }}" class="fw-semibold text-decoration-none">
                            {{ $account->bids_count }} {{ Str::plural('bid', $account->bids_count) }} <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </td>
                    <td class="small">{{ $account->last_scanned_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="text-nowrap">
                        <div class="d-flex gap-1">
                            <a href="{{ route('freelancer.bids.index', ['account_id' => $account->id]) }}" class="btn btn-sm btn-outline-info" title="View bids for {{ $account->name }}"><i class="bi bi-send"></i></a>
                            <form action="{{ route('freelancer.accounts.test', $account) }}" method="POST" class="test-connection-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Test connection"><i class="bi bi-plug"></i></button>
                            </form>
                            <form action="{{ route('freelancer.accounts.scan', $account) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Scan now"><i class="bi bi-search"></i></button>
                            </form>
                            <a href="{{ route('freelancer.accounts.edit', $account) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('freelancer.accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Remove this account?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-person-badge"></i><h6 class="mt-2 fw-bold">No Freelancer accounts yet</h6><p>Add an account with its OAuth key to start automatic bidding.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.test-connection-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const token = this.querySelector('input[name="_token"]').value;
        fetch(this.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
            .then(async res => {
                const data = await res.json();
                if (res.ok) toastr.success(data.success); else toastr.error(data.error);
            })
            .catch(err => toastr.error('Error: ' + err.message));
    });
});
</script>
@endsection
