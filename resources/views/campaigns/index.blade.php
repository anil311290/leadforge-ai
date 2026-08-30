@extends('layouts.app')
@section('title', 'Discovery Campaigns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Discovery Campaigns</h4>
        <p class="text-muted small mb-0">Every "Find Projects" run you've started.</p>
    </div>
    <a href="{{ route('campaigns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Find Projects</a>
</div>

@if($campaigns->count())
<div class="row g-3">
    @foreach($campaigns as $campaign)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0"><a href="{{ route('campaigns.show', $campaign) }}">{{ $campaign->name }}</a></h6>
                    @php $badge = ['running'=>'info','paused'=>'warning','completed'=>'success','failed'=>'danger'][$campaign->status] ?? 'secondary'; @endphp
                    <span class="badge text-bg-{{ $badge }}">{{ ucfirst($campaign->status) }}</span>
                </div>
                <div class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $campaign->location }}</div>
                <div class="d-flex gap-3 small">
                    <div><span class="fw-bold">{{ $campaign->leads_count }}</span> leads</div>
                    <div><i class="bi bi-clock me-1"></i>{{ $campaign->created_at->diffForHumans() }}</div>
                </div>
                @if($campaign->error)
                    <div class="alert alert-danger py-1 small mt-2 mb-0">{{ $campaign->error }}</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-3">{{ $campaigns->links() }}</div>
@else
<div class="card shadow-sm">
    <div class="empty-state">
        <i class="bi bi-bullseye"></i>
        <h6 class="fw-bold mt-3">No campaigns yet</h6>
        <p>Start your first discovery run and let the engine find your ideal projects.</p>
        <a href="{{ route('campaigns.create') }}" class="btn btn-primary"><i class="bi bi-rocket-takeoff me-1"></i> Find Projects</a>
    </div>
</div>
@endif
@endsection