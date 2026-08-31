@extends('layouts.app')
@section('title', 'Leads')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Leads</h4>
        <p class="text-muted small mb-0">{{ $counts['all'] }} total · {{ $counts['qualified'] }} qualified · {{ $counts['hot'] }} hot</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leads.import') }}" class="btn btn-outline-secondary"><i class="bi bi-upload me-1"></i> Import</a>
        <a href="{{ route('leads.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Lead</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search company, industry, city..." value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['DISCOVERED','NEW','ANALYZED','QUALIFIED','CONTACTED','REPLIED','INTERESTED','MEETING','PROPOSAL','NEGOTIATION','WON','LOST','NOT_INTERESTED','DO_NOT_CONTACT'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="score_class" class="form-select form-select-sm">
                    <option value="">All scores</option>
                    @foreach(['HOT','HIGH','MEDIUM','LOW','IGNORE'] as $s)
                        <option value="{{ $s }}" @selected(request('score_class') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light flex-fill">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Company</th><th>Industry</th><th>Location</th><th>Service</th><th>Score</th><th>Est. Value</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($leads as $lead)
                <tr>
                    <td>
                        <a href="{{ route('leads.show', $lead) }}" class="fw-semibold">{{ $lead->company }}</a>
                        @if($lead->normalized_domain)<div class="small text-muted">{{ $lead->normalized_domain }}</div>@endif
                    </td>
                    <td class="small">{{ Str::limit($lead->industry ?? '—', 24) }}</td>
                    <td class="small">{{ $lead->city ?? $lead->location ?? '—' }}</td>
                    <td class="small">{{ Str::limit($lead->recommended_service ?? '—', 18) }}</td>
                    <td>
                        @if($lead->opportunity_score !== null)
                            <span class="badge {{ match($lead->score_class){'HOT'=>'badge-hot','HIGH'=>'badge-high','MEDIUM'=>'badge-medium','LOW'=>'badge-low',default=>'badge-ignore'} }}">{{ $lead->opportunity_score }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small">{{ $lead->estimated_max ? '₹'.number_format($lead->estimated_max) : '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $lead->status }}</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('leads.show', $lead) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="{{ route('opportunities.show', $lead) }}"><i class="bi bi-lightning-charge me-2"></i>Opportunity</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="delete-form" data-confirm="Delete this lead?">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger" type="button" onclick="confirmDelete(this)"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-people"></i><h6 class="mt-2 fw-bold">No leads found</h6><p>Run a <a href="{{ route('campaigns.create') }}">discovery campaign</a> or import leads.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())<div class="card-footer">{{ $leads->links() }}</div>@endif
</div>
@endsection