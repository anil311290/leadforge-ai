@extends('layouts.app')
@section('title', 'Audit Trail')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Audit Trail</h4><p class="text-muted small mb-0">Every important action in the system.</p></div>
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search action" value="{{ request('q') }}">
        <button class="btn btn-sm btn-primary">Search</button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Time</th><th>User</th><th>Entity</th><th>Action</th><th>ID</th><th>Details</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="small">
                    <td class="text-muted">{{ $log->created_at->diffForHumans() }}</td>
                    <td>{{ $log->user->name ?? 'system' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $log->entity }}</span></td>
                    <td class="fw-semibold">{{ $log->action }}</td>
                    <td>{{ $log->entity_id ?? '—' }}</td>
                    <td class="text-muted">{{ Str::limit(implode(' ', (array) $log->details ?? []), 60) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No audit records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
</div>
@endsection
