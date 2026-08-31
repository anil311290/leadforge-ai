@extends('layouts.app')
@section('title', 'Follow-ups')

@section('content')
<div class="mb-3">
    <h4 class="fw-bold mb-1">Follow-ups</h4>
    <p class="text-muted small mb-0">Automated re-engagement after each send.</p>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-danger">{{ $summary['overdue'] }}</div><div class="text-muted small">Due now</div></div></div>
    <div class="col-md-4"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-warning">{{ $summary['scheduled'] }}</div><div class="text-muted small">Scheduled</div></div></div>
    <div class="col-md-4"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-success">{{ $summary['sent'] }}</div><div class="text-muted small">Sent</div></div></div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Lead</th><th>Seq</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($followUps as $fu)
                <tr>
                    <td>@if($fu->lead)<a href="{{ route('leads.show', $fu->lead) }}">{{ Str::limit($fu->lead->company, 30) }}</a>@else<span class="text-muted">Deleted lead</span>@endif</td>
                    <td><span class="badge bg-dark text-white">{{ $fu->sequence_number }}</span></td>
                    <td class="small">{{ $fu->scheduled_at }}</td>
                    <td><span class="badge {{ $fu->status==='pending'?'text-bg-warning':'bg-light text-dark border' }}">{{ $fu->status }}</span></td>
                    <td>@if($fu->status==='pending')<form method="POST" action="{{ route('followups.complete', $fu) }}">@csrf<button class="btn btn-sm btn-light">Mark done</button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No follow-ups yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($followUps->hasPages())<div class="card-footer">{{ $followUps->links() }}</div>@endif
</div>
@endsection

