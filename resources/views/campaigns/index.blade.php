@extends('layouts.app')
@section('title', 'Discovery Campaigns')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Discovery Campaigns</h4>
        <p class="text-muted small mb-0"><i class="bi bi-bullseye me-1"></i>Every "Find Projects" run you've started.</p>
    </div>
    <a href="{{ route('campaigns.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-1"></i> New Discovery
    </a>
</div>

@if($campaigns->count())
<div class="row g-3">
    @foreach($campaigns as $campaign)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100 border-0 campaign-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-decoration-none text-dark stretched-link">
                            {{ $campaign->name }}
                        </a>
                    </h6>
                    @php
                        $badge = [
                            'running' => ['info', 'arrow-repeat', 'Running'],
                            'paused' => ['warning', 'pause', 'Paused'],
                            'completed' => ['success', 'check-circle', 'Completed'],
                            'failed' => ['danger', 'x-circle', 'Failed'],
                        ][$campaign->status] ?? ['secondary', 'question-circle', ucfirst($campaign->status)];
                    @endphp
                    <span class="badge bg-{{ $badge[0] }} bg-opacity-10 text-{{ $badge[0] }} border border-{{ $badge[0] }} border-opacity-25 rounded-pill px-2 py-1 small fw-semibold">
                        <i class="bi bi-{{ $badge[1] }} me-1"></i>{{ $badge[2] }}
                    </span>
                </div>
                <div class="small text-muted mb-3">
                    <i class="bi bi-geo-alt me-1"></i>{{ $campaign->location }}
                    @if($campaign->radius_km)<span class="ms-2"><i class="bi bi-broadcast me-1"></i>{{ $campaign->radius_km }} km</span>@endif
                </div>
                <div class="d-flex align-items-center gap-4 small border-top pt-3">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-building text-primary"></i>
                        <span class="fw-semibold">{{ $campaign->leads_count }}</span>
                        <span class="text-muted">leads</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-clock text-muted"></i>
                        <span class="text-muted">{{ $campaign->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if($campaign->error)
                    <div class="alert alert-danger py-1 small mt-2 mb-0 d-flex align-items-center gap-1">
                        <i class="bi bi-exclamation-triangle"></i> {{ $campaign->error }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4 d-flex justify-content-center">{{ $campaigns->links() }}</div>
@else
<div class="card shadow-sm border-0">
    <div class="empty-state py-5">
        <div class="mb-3">
            <i class="bi bi-bullseye" style="font-size:3rem;color:#c2cad6;"></i>
        </div>
        <h5 class="fw-bold">No campaigns yet</h5>
        <p class="text-muted mb-3">Start your first discovery run and let the engine find your ideal projects.</p>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-lg px-4">
            <i class="bi bi-rocket-takeoff me-1"></i> Start Discovery
        </a>
    </div>
</div>
@endif
@endsection