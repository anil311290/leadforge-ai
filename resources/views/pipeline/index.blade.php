@extends('layouts.app')
@section('title', 'Pipeline')

@section('content')
<div class="mb-3">
    <h4 class="fw-bold mb-1">Pipeline</h4>
    <p class="text-muted small mb-0">Stage board — move leads through the sales journey.</p>
</div>

<div style="overflow-x:auto; display:flex; gap:1rem; padding-bottom:.5rem;">
@foreach($columns as $status => $label)
    @php $stageLeads = $leadsByStatus->get($status, collect()); @endphp
    <div style="min-width:240px;width:240px;background:#eef1f6;border-radius:.8rem;padding:.6rem;">
        <div class="d-flex justify-content-between align-items-center px-1 pb-2">
            <span class="fw-semibold small">{{ $label }}</span>
            <span class="badge bg-white text-muted border">{{ $stageLeads->count() }}</span>
        </div>
        <div class="d-flex flex-column gap-2">
        @foreach($stageLeads as $lead)
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <div class="fw-semibold small"><a href="{{ route('leads.show', $lead) }}" class="text-decoration-none">{{ Str::limit($lead->company, 28) }}</a></div>
                    @if($lead->opportunity_score !== null)
                        <div class="mt-1"><span class="badge {{ $lead->score_class ? 'badge-'.strtolower($lead->score_class) : 'badge-ignore' }}">{{ $lead->opportunity_score }}</span>
                        @if($lead->estimated_max)<span class="small text-muted ms-1">₹{{ number_format($lead->estimated_max) }}</span>@endif</div>
                    @endif
                    <div class="small text-muted">{{ Str::limit($lead->recommended_service ?? $lead->industry ?? '—', 26) }}</div>
                    <form method="POST" action="{{ route('pipeline.move', $lead) }}" class="mt-2">
                        @csrf
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($columns as $s => $l)
                                <option value="{{ $s }}" @selected($lead->status === $s)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        @endforeach
        @if($stageLeads->isEmpty())
            <div class="text-center text-muted small py-3">Empty</div>
        @endif
        </div>
    </div>
@endforeach
</div>
@endsection
