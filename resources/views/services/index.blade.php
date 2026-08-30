@extends('layouts.app')
@section('title', 'Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Services Catalog</h4><p class="text-muted small mb-0">The services the engine uses for opportunity matching &amp; value estimation.</p></div>
    <a href="{{ route('services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Service</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Service</th><th>Category</th><th>Value range</th><th>Rules</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td><a href="{{ route('services.show', $service) }}" class="fw-semibold">{{ $service->name }}</a></td>
                    <td class="small">{{ $service->category ?? '—' }}</td>
                    <td class="small">@if($service->min_value)₹{{ number_format($service->min_value) }} – ₹{{ number_format($service->max_value) }}@else—@endif</td>
                    <td class="small">{{ $service->rules_count }}</td>
                    <td><span class="badge {{ $service->is_active?'text-bg-success':'bg-light text-dark border' }}">{{ $service->is_active?'Active':'Inactive' }}</span></td>
                    <td><a href="{{ route('services.show', $service) }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-boxes"></i><h6 class="mt-2 fw-bold">No services yet</h6><p>Add the software services you offer so the engine can match businesses to opportunities.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())<div class="card-footer">{{ $services->links() }}</div>@endif
</div>
@endsection
