@extends('layouts.app')
@section('title', $campaign->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <a href="{{ route('campaigns.index') }}" class="small text-muted">&larr; Campaigns</a>
        <h4 class="fw-bold mb-0">{{ $campaign->name }}</h4>
        <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $campaign->location }} @if($campaign->radius_km)· {{ $campaign->radius_km }} km @endif</p>
    </div>
    <div class="d-flex gap-2">
        @if($campaign->status === 'running')
            <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf<button class="btn btn-outline-warning btn-sm">Pause</button></form>
        @elseif($campaign->status === 'paused')
            <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf<button class="btn btn-success btn-sm">Resume</button></form>
        @endif
        @if(in_array($campaign->status, ['running','paused']))
            <form method="POST" action="{{ route('campaigns.cancel', $campaign) }}">@csrf<button class="btn btn-outline-danger btn-sm">Cancel</button></form>
        @endif
    </div>
</div>

@if($campaign->error)
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $campaign->error }}</div>
@endif

<div class="row g-3 mb-4">
    @php
        $tiles = [
            ['Businesses discovered', $stats['businesses_discovered'], 'bi-building', 'primary'],
            ['Websites found', $stats['websites_found'], 'bi-globe', 'info'],
            ['Websites scanned', $stats['websites_scanned'], 'bi-search', 'secondary'],
            ['Analysed', $stats['analysed'], 'bi-motherboard', 'warning'],
            ['Hot & High', $stats['hot'], 'bi-fire', 'danger'],
            ['Pipeline value', '₹'.number_format($stats['pipeline']), 'bi-cash-stack', 'success'],
        ];
    @endphp
    @foreach($tiles as $t)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card p-3 shadow-sm text-center">
            <div class="stat-icon mx-auto bg-{{ $t[3] }} bg-opacity-10 text-{{ $t[3] }} mb-2"><i class="bi {{ $t[2] }}"></i></div>
            <div class="fw-bold">{{ $t[1] }}</div>
            <div class="text-muted small">{{ $t[0] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Leads in this campaign</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Company</th><th>Industry</th><th>Service</th><th>Score</th><th>Est. Value</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($leads as $lead)
                <tr>
                    <td><a href="{{ route('leads.show', $lead) }}">{{ $lead->company }}</a>@if($lead->location)<div class="small text-muted">{{ $lead->location }}</div>@endif</td>
                    <td class="small">{{ $lead->industry ?? '—' }}</td>
                    <td class="small">{{ $lead->recommended_service ?? '—' }}</td>
                    <td>@if($lead->opportunity_score)<span class="badge {{ $lead->score_class ? 'badge-'.strtolower($lead->score_class) : 'badge-ignore' }}">{{ $lead->opportunity_score }}</span>@else<span class="badge bg-light text-muted border">—</span>@endif</td>
                    <td class="small">{{ $lead->estimated_max ? '₹'.number_format($lead->estimated_max) : '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $lead->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No leads yet — discovery is still in progress.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())<div class="card-footer">{{ $leads->links() }}</div>@endif
</div>
@endsection