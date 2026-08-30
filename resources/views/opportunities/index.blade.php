@extends('layouts.app')
@section('title', 'Opportunities')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Opportunities</h4><p class="text-muted small mb-0">The most promising businesses, ranked by AI opportunity score.</p></div>
</div>

<div class="card shadow-sm mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4"><select name="service" class="form-select form-select-sm"><option value="">All services</option>@foreach($services as $svc)<option value="{{ $svc }}" @selected(request('service') === $svc)>{{ $svc }}</option>@endforeach</select></div>
        <div class="col-md-3"><input type="number" name="min_score" class="form-control form-control-sm" placeholder="Min score" value="{{ request('min_score') }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-sm btn-primary flex-fill">Filter</button><a href="{{ route('opportunities.index') }}" class="btn btn-sm btn-light">Reset</a></div>
    </form>
</div></div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Company</th><th>Service</th><th>Score</th><th>Confidence</th><th>Est. Value</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($leads as $lead)
                <tr>
                    <td><a href="{{ route('leads.show', $lead) }}" class="fw-semibold">{{ Str::limit($lead->company, 30) }}</a>
                        <div class="small text-muted">{{ $lead->industry ?? '—' }} · {{ $lead->city ?? '' }}</div></td>
                    <td class="small">{{ $lead->recommended_service ?? '—' }}</td>
                    <td><span class="badge {{ $lead->score_class ? 'badge-'.strtolower($lead->score_class) : 'badge-ignore' }}">{{ $lead->opportunity_score }}</span></td>
                    <td class="small">{{ $lead->confidence ?? '—' }}%</td>
                    <td class="small">{{ $lead->estimated_max ? '₹'.number_format($lead->estimated_min).' – ₹'.number_format($lead->estimated_max) : '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $lead->status }}</span></td>
                    <td><a href="{{ route('opportunities.show', $lead) }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-lightning-charge"></i><h6 class="mt-2 fw-bold">No opportunities yet</h6><p>Run <a href="{{ route('campaigns.create') }}">discovery</a> to analyse and rank businesses.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())<div class="card-footer">{{ $leads->links() }}</div>@endif
</div>
@endsection
