@extends('layouts.app')

@section('title', 'My Websites')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold mb-1"><i class="bi bi-window-stack me-1"></i>Website Builder</div>
        <h4 class="fw-bold mb-1">Websites</h4>
        <p class="text-muted small mb-0">Manage drafts, published sites and public URLs from one place.</p>
    </div>
    <a href="{{ route('website-builder.websites.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Website</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <form method="GET" class="p-3">
        <div class="row g-2 align-items-center">
            <div class="col-lg-7">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" class="form-control border-start-0" value="{{ request('q') }}" placeholder="Search business, city or public URL...">
                </div>
            </div>
            <div class="col-sm-5 col-lg-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['draft', 'published', 'unpublished', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-7 col-lg-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1" type="submit">Filter</button>
                @if(request()->hasAny(['q', 'status']))<a href="{{ route('website-builder.websites.index') }}" class="btn btn-light" title="Clear filters"><i class="bi bi-x-lg"></i></a>@endif
            </div>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0 p-3">
    @if($websites->count())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small text-muted">Showing {{ $websites->firstItem() }}–{{ $websites->lastItem() }} of {{ $websites->total() }} websites</span>
            @if(request()->hasAny(['q', 'status']))<span class="badge bg-primary-subtle text-primary">Filtered results</span>@endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 760px;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Business</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Public URL</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($websites as $website)
                        <tr>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 250px;" title="{{ $website->business_name }}">{{ $website->business_name }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 250px;" title="{{ $website->business_description }}">{{ $website->business_description ? Str::limit($website->business_description, 70) : 'No description yet' }}</div>
                            </td>
                            <td class="small text-muted">{{ collect([$website->city, $website->state])->filter()->join(', ') ?: '—' }}</td>
                            <td><span class="badge rounded-pill {{ $website->status === 'published' ? 'bg-success-subtle text-success' : 'bg-light text-dark border' }}">{{ ucfirst($website->status) }}</span></td>
                            <td><a href="{{ $website->public_url }}" target="_blank" class="small text-decoration-none text-truncate d-inline-block" style="max-width: 190px;" title="{{ $website->public_url }}">{{ $website->slug }}</a></td>
                            <td class="text-end">
                                <div class="btn-group dropstart">
                                    <a href="{{ route('website-builder.websites.edit', $website) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-label="More actions"></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('website-builder.media.index', $website) }}"><i class="bi bi-images me-2"></i>Gallery</a></li>
                                        @if($website->status !== 'published')<li><form method="POST" action="{{ route('website-builder.websites.publish', $website) }}">@csrf<button class="dropdown-item" type="submit"><i class="bi bi-globe2 me-2"></i>Publish</button></form></li>@endif
                                        @if($website->status === 'published')<li><form method="POST" action="{{ route('website-builder.websites.unpublish', $website) }}">@csrf<button class="dropdown-item" type="submit"><i class="bi bi-eye-slash me-2"></i>Unpublish</button></form></li>@endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $websites->links() }}</div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-window-stack display-5 text-muted"></i>
            <h5 class="mt-3">{{ request()->hasAny(['q', 'status']) ? 'No matching websites' : 'No websites yet' }}</h5>
            <p class="text-muted small mb-3">{{ request()->hasAny(['q', 'status']) ? 'Try a different search or clear the filters.' : 'Create your first website draft to get started.' }}</p>
            @if(request()->hasAny(['q', 'status']))<a href="{{ route('website-builder.websites.index') }}" class="btn btn-outline-secondary btn-sm">Clear filters</a>@else<a href="{{ route('website-builder.websites.create') }}" class="btn btn-primary btn-sm">Create Website</a>@endif
        </div>
    @endif
</div>
@endsection
