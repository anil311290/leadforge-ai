@extends('layouts.app')

@section('title', 'Website Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Website Builder</h4>
        <p class="text-muted small mb-0">Create, publish, and manage business websites.</p>
    </div>
    <a href="{{ route('website-builder.websites.create') }}" class="btn btn-primary">Create Website</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="text-muted small">Total Websites</div>
            <div class="fs-3 fw-bold">{{ $websites->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="text-muted small">Published</div>
            <div class="fs-3 fw-bold">{{ $websites->where('status', 'published')->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3 h-100">
            <div class="text-muted small">Drafts</div>
            <div class="fs-3 fw-bold">{{ $websites->where('status', 'draft')->count() }}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Recent Websites</h6>
        <a href="{{ route('website-builder.websites.index') }}" class="small">View all</a>
    </div>

    @if($websites->count())
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Status</th>
                        <th>Slug</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($websites as $website)
                        <tr>
                            <td>{{ $website->business_name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $website->status }}</span></td>
                            <td>{{ $website->slug }}</td>
                            <td>{{ $website->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-muted">No websites created yet.</div>
    @endif
</div>
@endsection
