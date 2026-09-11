@extends('layouts.app')
@section('title', 'Leads')

@section('content')
<style>
@media (min-width: 768px) {
    .leads-table-wrap {
        overflow: visible;
    }
}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-people text-primary"></i> Leads
        </h4>
        <p class="text-muted small mb-0 d-flex align-items-center gap-2">
            <span class="fw-semibold">{{ $counts['all'] }}</span> total
            <span class="text-secondary">·</span>
            <span class="fw-semibold text-success">{{ $counts['qualified'] }}</span> qualified
            <span class="text-secondary">·</span>
            <span class="fw-semibold text-danger">{{ $counts['hot'] }}</span> hot
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('leads.import') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload me-1"></i> Import</a>
        <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Lead</a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-search me-1"></i>Search</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Company, industry, city..." value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-tag me-1"></i>Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['DISCOVERED','NEW','ANALYZED','QUALIFIED','CONTACTED','REPLIED','INTERESTED','MEETING','PROPOSAL','NEGOTIATION','WON','LOST','NOT_INTERESTED','DO_NOT_CONTACT'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(strtolower($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-fire me-1"></i>Score</label>
                <select name="score_class" class="form-select form-select-sm">
                    <option value="">All scores</option>
                    @foreach(['HOT','HIGH','MEDIUM','LOW','IGNORE'] as $s)
                        <option value="{{ $s }}" @selected(request('score_class') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-diagram-3 me-1"></i>Campaign</label>
                <select name="campaign_id" class="form-select form-select-sm">
                    <option value="">All campaigns</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((string) request('campaign_id') === (string) $campaign->id)>{{ Str::limit($campaign->name, 32) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-globe me-1"></i>Website</label>
                <select name="website" class="form-select form-select-sm">
                    <option value="">Any website</option>
                    <option value="has_website" @selected(request('website') === 'has_website')>Has website</option>
                    <option value="no_website" @selected(request('website') === 'no_website')>No website</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-person-lines-fill me-1"></i>Contact</label>
                <select name="contact" class="form-select form-select-sm">
                    <option value="">Any contact</option>
                    <option value="has_any" @selected(request('contact') === 'has_any')>Email or phone available</option>
                    <option value="has_email" @selected(request('contact') === 'has_email')>Has email</option>
                    <option value="no_email" @selected(request('contact') === 'no_email')>No email</option>
                    <option value="has_phone" @selected(request('contact') === 'has_phone')>Has phone</option>
                    <option value="no_phone" @selected(request('contact') === 'no_phone')>No phone</option>
                    <option value="missing_all" @selected(request('contact') === 'missing_all')>No email and no phone</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-pin-map me-1"></i>City/Area</label>
                <input type="text" name="city" class="form-control form-control-sm" placeholder="Singrauli..." value="{{ request('city') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-box me-1"></i>Industry</label>
                <input type="text" name="industry" class="form-control form-control-sm" placeholder="Retail..." value="{{ request('industry') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-download me-1"></i>Source</label>
                <select name="source" class="form-select form-select-sm">
                    <option value="">All sources</option>
                    @foreach($sources as $source)
                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ str_replace('_', ' ', ucfirst($source)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-trophy me-1"></i>Min score</label>
                <input type="number" min="0" max="100" name="min_score" class="form-control form-control-sm" value="{{ request('min_score') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-list-ol me-1"></i>Show</label>
                <select name="per_page" class="form-select form-select-sm">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Filter</button>
                @if(collect(['q','status','score_class','campaign_id','website','contact','city','industry','source','min_score','per_page'])->contains(fn ($key) => request()->filled($key) && ! ($key === 'per_page' && request('per_page') == 10)))
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-x-lg me-1"></i>Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <span class="fw-semibold small text-muted">
            <i class="bi bi-list-check me-1"></i>{{ $leads->total() }} leads found
        </span>
        <span class="badge bg-light text-muted border">{{ $leads->currentPage() }}/{{ $leads->lastPage() }} pages</span>
    </div>
    <div class="table-responsive leads-table-wrap">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3"><i class="bi bi-building me-1"></i>Company</th>
                    <th><i class="bi bi-box me-1"></i>Industry</th>
                    <th><i class="bi bi-geo-alt me-1"></i>Location</th>
                    <th><i class="bi bi-tools me-1"></i>Service</th>
                    <th><i class="bi bi-trophy me-1"></i>Score</th>
                    <th><i class="bi bi-cash me-1"></i>Est. Value</th>
                    <th><i class="bi bi-flag me-1"></i>Status</th>
                    <th class="pe-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($leads as $lead)
                @php
                    $statusColors = [
                        'DISCOVERED' => 'bg-secondary',
                        'NEW' => 'bg-info',
                        'ANALYZED' => 'bg-primary',
                        'QUALIFIED' => 'bg-success',
                        'CONTACTED' => 'bg-warning text-dark',
                        'REPLIED' => 'bg-info',
                        'INTERESTED' => 'bg-success',
                        'MEETING' => 'bg-primary',
                        'PROPOSAL' => 'bg-warning text-dark',
                        'NEGOTIATION' => 'bg-warning text-dark',
                        'WON' => 'bg-success',
                        'LOST' => 'bg-danger',
                        'NOT_INTERESTED' => 'bg-secondary',
                        'DO_NOT_CONTACT' => 'bg-dark',
                    ];
                    $statusColor = $statusColors[$lead->status] ?? 'bg-light text-dark border';
                    $whatsappPhone = \App\Services\Discovery\DataNormalizer::normalizeWhatsappPhone($lead->phone);
                    $googleSearchUrl = 'https://www.google.com/search?q='.urlencode(trim($lead->company.' '.$lead->city.' '.$lead->location));
                @endphp
                <tr class="align-middle">
                    <td class="ps-3">
                        <a href="{{ route('leads.show', $lead) }}" class="fw-semibold text-decoration-none">{{ $lead->company }}</a>
                        @if($lead->normalized_domain)
                            <div class="small text-muted" style="font-size:11px;">{{ $lead->normalized_domain }}</div>
                        @endif
                        @if($lead->email)
                            <div class="small text-muted" style="font-size:11px;"><i class="bi bi-envelope me-1"></i>{{ $lead->email }}</div>
                        @endif
                        @if($lead->phone && $whatsappPhone)
                            <div class="small text-muted" style="font-size:11px;"><i class="bi bi-telephone me-1"></i><a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" class="text-muted text-decoration-none">{{ $lead->phone }} <i class="bi bi-whatsapp text-success" style="font-size:10px;"></i></a></div>
                        @endif
                    </td>
                    <td class="small">{{ Str::limit($lead->industry ?? '—', 20) }}</td>
                    <td class="small text-muted">{{ $lead->city ?? $lead->location ?? '—' }}</td>
                    <td>
                        @if($lead->recommended_service)
                            <span class="small fw-medium">{{ Str::limit($lead->recommended_service, 16) }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($lead->opportunity_score !== null)
                            <span class="badge rounded-pill px-2 py-1 {{ match($lead->score_class){'HOT'=>'bg-danger','HIGH'=>'bg-warning text-dark','MEDIUM'=>'bg-info','LOW'=>'bg-secondary',default=>'bg-light text-dark border'} }}">
                                {{ $lead->opportunity_score }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small fw-medium">{{ $lead->estimated_max ? '₹'.number_format($lead->estimated_max) : '—' }}</td>
                    <td>
                        <span class="badge rounded-pill px-2 py-1 {{ $statusColor }}">{{ $lead->status }}</span>
                    </td>
                    <td class="pe-3 text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border rounded-3 px-2" data-bs-toggle="dropdown" title="Actions">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('leads.show', $lead) }}"><i class="bi bi-eye me-2 text-primary"></i>View Details</a></li>
                                <li><a class="dropdown-item" href="{{ route('leads.edit', $lead) }}"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Lead</a></li>
                                <li><a class="dropdown-item" href="{{ route('leads.show', $lead) }}#overview"><i class="bi bi-flag me-2 text-success"></i>Update Status</a></li>
                                <li>
                                    <form method="POST" action="{{ route('quotations.generate', $lead) }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item" type="submit"><i class="bi bi-file-earmark-text me-2 text-dark"></i>Generate Quotation</button>
                                    </form>
                                </li>
                                <li><a class="dropdown-item" href="{{ $googleSearchUrl }}" target="_blank" rel="noopener"><i class="bi bi-google me-2 text-danger"></i>Google Search</a></li>
                                <li><a class="dropdown-item" href="{{ route('opportunities.show', $lead) }}"><i class="bi bi-lightning-charge me-2 text-warning"></i>Opportunity</a></li>
                                @if($lead->email)
                                <li>
                                    <form method="POST" action="{{ route('emails.generate', $lead) }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item" type="submit"><i class="bi bi-envelope me-2 text-info"></i>Generate Email</button>
                                    </form>
                                </li>
                                @endif
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
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-5">
                            <i class="bi bi-people" style="font-size:2.5rem;color:#c2cad6;"></i>
                            <h6 class="fw-bold mt-3">No leads found</h6>
                            <p class="text-muted small mb-3">Run a discovery campaign or import leads to get started.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-bullseye me-1"></i>Find Projects</a>
                                <a href="{{ route('leads.import') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload me-1"></i>Import</a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
        <div class="card-footer border-top-0 py-3 bg-white">
            <nav aria-label="Leads pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                    @if ($leads->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link border rounded px-3 py-1 small text-muted bg-light"><i class="bi bi-chevron-left"></i> Previous</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link border rounded px-3 py-1 small text-dark bg-white" href="{{ $leads->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i> Previous</a>
                        </li>
                    @endif

                    @foreach ($leads->getUrlRange(max(1, $leads->currentPage() - 2), min($leads->lastPage(), $leads->currentPage() + 2)) as $page => $url)
                        <li class="page-item {{ $page === $leads->currentPage() ? 'active' : '' }}">
                            <a class="page-link border rounded px-3 py-1 small {{ $page === $leads->currentPage() ? 'bg-primary text-white border-primary' : 'bg-white text-dark' }}" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($leads->hasMorePages())
                        <li class="page-item">
                            <a class="page-link border rounded px-3 py-1 small text-dark bg-white" href="{{ $leads->nextPageUrl() }}" rel="next">Next <i class="bi bi-chevron-right"></i></a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link border rounded px-3 py-1 small text-muted bg-light">Next <i class="bi bi-chevron-right"></i></span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
</div>
@endsection