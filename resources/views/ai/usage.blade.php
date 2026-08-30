@extends('layouts.app')
@section('title', 'AI Usage')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">AI Usage</h4><p class="text-muted small mb-0">Every model call the engine made.</p></div>
    <form method="GET">
        <select name="provider" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All providers</option>
            @foreach(['openai','openrouter','ollama'] as $p)<option value="{{ $p }}" @selected(request('provider') === $p)>{{ $p }}</option>@endforeach
        </select>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">{{ number_format($totals['requests']) }}</div><div class="text-muted small">Requests</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">${{ number_format($totals['total_cost'], 3) }}</div><div class="text-muted small">Est. cost</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">{{ $totals['avg_duration_ms'] }}ms</div><div class="text-muted small">Avg duration</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">{{ $totals['prompts']->count() }}</div><div class="text-muted small">Prompt types</div></div></div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Time</th><th>User</th><th>Lead</th><th>Provider</th><th>Model</th><th>Prompt</th><th>Status</th><th>Duration</th><th>Cost</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="small">
                    <td>{{ $log->created_at->diffForHumans() }}</td>
                    <td>{{ $log->user->name ?? '—' }}</td>
                    <td>@if($log->lead)<a href="{{ route('leads.show', $log->lead) }}">{{ Str::limit($log->lead->company, 20) }}</a>@else—@endif</td>
                    <td><span class="badge bg-light text-dark border">{{ $log->provider ?? 'heuristic' }}</span></td>
                    <td>{{ $log->model ?? 'rules' }}</td>
                    <td>{{ $log->prompt_name }}</td>
                    <td><span class="badge {{ $log->status==='completed'?'text-bg-success':'text-bg-danger' }}">{{ $log->status }}</span></td>
                    <td>{{ $log->duration_ms }}ms</td>
                    <td>${{ number_format($log->cost, 4) }}</td>
                </tr>
            @empty
                <tr><td colspan="9"><div class="empty-state"><i class="bi bi-cpu"></i><h6 class="mt-2 fw-bold">No usage yet</h6><p>Analysis calls are logged here.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
</div>
@endsection
