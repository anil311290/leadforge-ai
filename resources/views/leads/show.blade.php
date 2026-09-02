@extends('layouts.app')
@section('title', $lead->company)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <a href="{{ route('leads.index') }}" class="small text-muted">&larr; Leads</a>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0">{{ $lead->company }}</h3>
            @if($lead->score_class)
                <span class="badge {{ match($lead->score_class){'HOT'=>'badge-hot','HIGH'=>'badge-high','MEDIUM'=>'badge-medium','LOW'=>'badge-low',default=>'badge-ignore'} }}">{{ $lead->score_class }}</span>
            @endif
        </div>
        <p class="text-muted small mb-0">
            @if($lead->normalized_domain)<a href="{{ $lead->website }}" target="_blank" rel="noopener">{{ $lead->normalized_domain }}</a> · @endif
            {{ $lead->industry ?? '—' }} · {{ $lead->city ?? $lead->location ?? '' }}
            @if($lead->email) · <i class="bi bi-envelope"></i> <a href="mailto:{{ $lead->email }}" class="text-muted text-decoration-none">{{ $lead->email }}</a>@endif
            @if($lead->phone) · <i class="bi bi-telephone"></i> <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-muted text-decoration-none" title="Open WhatsApp">{{ $lead->phone }} <i class="bi bi-whatsapp text-success"></i></a>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(!$lead->owner_id)
            <form method="POST" action="{{ route('leads.claim', $lead) }}">@csrf<button class="btn btn-outline-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Claim</button></form>
        @endif
        @if($lead->normalized_domain && !$lead->analyses->count())
            <form method="POST" action="{{ route('leads.analyse', $lead) }}">@csrf<button class="btn btn-info btn-sm text-white"><i class="bi bi-motherboard me-1"></i>Analyse</button></form>
        @endif
        @if($lead->email)
            <form method="POST" action="{{ route('emails.generate', $lead) }}">@csrf<button class="btn btn-outline-primary btn-sm"><i class="bi bi-envelope-paper me-1"></i>Generate Email</button></form>
        @endif
        <form method="POST" action="{{ route('followups.trigger', $lead) }}">@csrf<button class="btn btn-outline-warning btn-sm"><i class="bi bi-alarm me-1"></i>Schedule Follow-up</button></form>
        <div class="dropdown">
            <button class="btn btn-light btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('opportunities.show', $lead) }}"><i class="bi bi-lightning-charge me-2"></i>Opportunity view</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="delete-form" data-confirm="Delete this lead?">
                        @csrf @method('DELETE')
                        <button class="dropdown-item text-danger" type="button" onclick="confirmDelete(this)"><i class="bi bi-trash me-2"></i>Delete</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="text-muted small">Score</div><div class="fs-4 fw-bold">{{ $lead->opportunity_score ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="text-muted small">Service</div><div class="fw-bold small">{{ $lead->recommended_service ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="text-muted small">Est. Value</div><div class="fw-bold">@if($lead->estimated_max)₹{{ number_format($lead->estimated_min) }}–₹{{ number_format($lead->estimated_max) }}@else—@endif</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="text-muted small">Status</div><div><span class="badge bg-light text-dark border">{{ $lead->status }}</span></div></div></div>
</div>

@if($lead->phone)
<div class="mb-3">
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="btn btn-success btn-sm">
        <i class="bi bi-whatsapp me-1"></i> Chat on WhatsApp — {{ $lead->phone }}
    </a>
</div>
@endif

<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="#overview" class="btn btn-sm btn-outline-primary active-tab" data-tab="overview"><i class="bi bi-info-circle me-1"></i>Details</a>
    <a href="#analysis" class="btn btn-sm btn-outline-primary" data-tab="analysis"><i class="bi bi-motherboard me-1"></i>Analysis</a>
    <a href="#website" class="btn btn-sm btn-outline-primary" data-tab="website"><i class="bi bi-globe me-1"></i>Website</a>
    <a href="#emails" class="btn btn-sm btn-outline-primary" data-tab="emails"><i class="bi bi-envelope me-1"></i>Emails</a>
    <a href="#followups" class="btn btn-sm btn-outline-primary" data-tab="followups"><i class="bi bi-alarm me-1"></i>Follow-ups</a>
    <a href="#activity" class="btn btn-sm btn-outline-primary" data-tab="activity"><i class="bi bi-activity me-1"></i>Activity</a>
</div>

<style>
.tab-btn.active-tab {
    background: #0d6efd !important;
    color: #fff !important;
    border-color: #0d6efd !important;
}
.tab-btn {
    transition: all .15s ease;
}
.tab-btn:hover {
    background: #e9ecef;
}
</style>
<script>
document.querySelectorAll('[data-tab]').forEach(link => {
    link.addEventListener('click', function(e) {
        document.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active-tab'));
        this.classList.add('active-tab');
    });
});
// Highlight active tab on page load based on hash
const hash = window.location.hash.replace('#', '');
if (hash) {
    const tab = document.querySelector('[data-tab="' + hash + '"]');
    if (tab) tab.classList.add('active-tab');
} else {
    document.querySelector('[data-tab="overview"]')?.classList.add('active-tab');
}
</script>
<div class="row g-3">
    <div class="col-12 col-xl-7">
        {{-- Analysis --}}
        <div class="card shadow-sm mb-3" id="analysis">
            <div class="card-header bg-white fw-bold">AI Analysis &amp; Recommendations</div>
            <div class="card-body">
                @if($lead->analysis && !isset($lead->analysis['error']))
                    <p class="small text-muted">{{ $lead->analysis['summary'] ?? '' }}</p>
                    <div class="row g-2">
                        <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $lead->confidence ?? 0 }}%</div><div class="text-muted small">Confidence</div></div></div>
                        <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $lead->data_quality ?? 0 }}</div><div class="text-muted small">Data Quality</div></div></div>
                        <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $lead->digital_maturity ?? 0 }}</div><div class="text-muted small">Digital Maturity</div></div></div>
                    </div>
                    <div class="mt-3">
                    @foreach($lead->recommendations as $rec)
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">{{ $rec->service_name }}
                                    @if($rec->service)<span class="badge bg-light text-dark border ms-1">{{ $rec->service->category }}</span>@endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="score-pill">{{ $rec->score }}/100</span>
                                    <span class="badge {{ $rec->score>=75?'badge-hot':($rec->score>=50?'badge-high':'badge-medium') }}">{{ $rec->score>=75?'Strong':($rec->score>=50?'Good':'Potential') }}</span>
                                </div>
                            </div>
                            <div class="small text-muted mt-1">Value: ₹{{ number_format($rec->estimated_min) }} – ₹{{ number_format($rec->estimated_max) }} · Confidence {{ $rec->confidence }}%</div>
                            @if($rec->inference)<div class="small mt-1"><strong>Why:</strong> {{ $rec->inference }}</div>@endif
                            @if($rec->evidence)
                                <div class="small mt-1"><strong>Evidence:</strong>
                                    @foreach(array_slice((array)$rec->evidence,0,4) as $ev)<span class="badge bg-light text-muted border me-1">{{ $ev }}</span>@endforeach
                                </div>
                            @endif
                            @if($rec->recommendation)<div class="small mt-1 text-accent"><i class="bi bi-lightbulb me-1"></i>{{ $rec->recommendation }}</div>@endif
                        </div>
                    @endforeach
                    </div>
                @else
                    <div class="empty-state py-4">
                        <i class="bi bi-motherboard"></i>
                        <p class="mt-2">No analysis yet.@if($lead->normalized_domain) <a href="{{ route('leads.analyse', $lead) }}" onclick="event.preventDefault();document.getElementById('analyse-form').submit();">Run analysis</a>
                        <form id="analyse-form" method="POST" action="{{ route('leads.analyse', $lead) }}" class="d-none">@csrf</form>@else Add a website to enable analysis.@endif</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Website scan --}}
        <div class="card shadow-sm mb-3" id="website">
            <div class="card-header bg-white fw-bold">Website Scan</div>
            <div class="card-body">
                @php $scan = $lead->scans->last(); @endphp
                @if($scan)
                    <div class="d-flex flex-wrap gap-3 small">
                        <div><span class="text-muted">Title:</span> {{ $scan->title ?? '—' }}</div>
                        <div><span class="text-muted">CMS:</span> {{ $scan->cms ?? '—' }}</div>
                        <div><span class="text-muted">Pages:</span> {{ $scan->page_count ?? 0 }}</div>
                        <div><span class="text-muted">HTTP:</span> {{ $scan->http_status ?? '—' }}</div>
                    </div>
                    @if($scan->business_data)
                        <hr>
                        <div class="small"><strong>Business data:</strong><pre class="bg-light p-2 rounded small mt-1">{{ json_encode($scan->business_data, JSON_PRETTY_PRINT) }}</pre></div>
                    @endif
                    @if(count($scan->pages ?? []))
                        <hr><div class="fw-semibold small mb-2">Pages</div>
                        <div class="row g-2">
                        @foreach($scan->pages->take(6) as $page)
                            <div class="col-12 col-md-6"><div class="border rounded p-2 small"><div class="fw-semibold">{{ $page->title ?? $page->path }}</div><div class="text-muted">{{ $page->path }}</div></div></div>
                        @endforeach
                        </div>
                    @endif
                @else
                    <div class="empty-state py-3"><i class="bi bi-search"></i><p class="mt-2">No website scan recorded.</p></div>
                @endif
            </div>
        </div>

        {{-- Activity --}}
        <div class="card shadow-sm" id="activity">
            <div class="card-header bg-white fw-bold">Activity</div>
            <div class="card-body py-2">
                <ul class="list-unstyled mb-0">
                @forelse($lead->activities as $activity)
                    <li class="py-2 border-bottom small">
                        <i class="bi bi-circle-fill text-muted me-1" style="font-size:.4rem"></i>
                        <strong>{{ $activity->title ?? $activity->type }}</strong>
                        <span class="text-muted">· {{ $activity->created_at->diffForHumans() }}</span>
                        @if($activity->user)<span class="text-muted">· {{ $activity->user->name }}</span>@endif
                    </li>
                @empty
                    <li class="text-muted small py-2">No activity yet.</li>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-3" id="overview">
            <div class="card-header bg-white fw-bold">Update Status</div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.update-status', $lead) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm">
                            @foreach(['DISCOVERED','NEW','ANALYZED','QUALIFIED','CONTACTED','REPLIED','INTERESTED','MEETING','PROPOSAL','NEGOTIATION','WON','LOST','NOT_INTERESTED','DO_NOT_CONTACT'] as $s)
                                <option value="{{ $s }}" @selected($lead->status === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" name="next_action" class="form-control form-control-sm mb-2" placeholder="Next action (optional)">
                    <button class="btn btn-sm btn-primary w-100">Update status</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('leads.add-note', $lead) }}">
                    @csrf
                    <label class="form-label small fw-semibold">Add note</label>
                    <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Add an internal note..."></textarea>
                    <button class="btn btn-sm btn-light w-100">Add note</button>
                </form>
                @if($lead->notes)
                    <div class="small text-muted mt-2"><strong>Notes:</strong><pre class="bg-light p-2 rounded small">{{ $lead->notes }}</pre></div>
                @endif
            </div>
        </div>
        <div class="card shadow-sm mb-3" id="emails">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Emails</span>
                @if($lead->email)
                    <form method="POST" action="{{ route('emails.generate', $lead) }}">@csrf<button class="btn btn-sm btn-primary"><i class="bi bi-magic me-1"></i>Generate draft</button></form>
                @endif
            </div>
            <div class="card-body py-2">
                @if($lead->email)
                    <div class="alert alert-info py-2 small mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-envelope"></i>
                        <span><strong>Client Email:</strong> <a href="mailto:{{ $lead->email }}" class="text-decoration-none">{{ $lead->email }}</a></span>
                    </div>
                @endif
                @forelse($lead->emails as $email)
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold">{{ Str::limit($email->subject, 42) }}</span>
                            <span class="badge {{ $email->status==='sent'?'text-bg-success':($email->status==='pending_approval'?'text-bg-warning':'bg-light text-dark border') }}">{{ $email->status }}</span>
                        </div>
                        <div class="small text-muted d-flex flex-wrap gap-2">
                            <span><i class="bi bi-clock me-1"></i>{{ $email->created_at->diffForHumans() }}</span>
                            <span><i class="bi bi-arrow-right me-1"></i>{{ $email->direction }}</span>
                            @if($email->to_email)
                                <span><i class="bi bi-person me-1"></i>To: {{ $email->to_email }}</span>
                            @endif
                        </div>
                        @if(in_array($email->status, ['pending_approval','approved']))
                            <div class="mt-2">
                                <form method="POST" action="{{ route('emails.approve', $email) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve &amp; queue</button></form>
                                <form method="POST" action="{{ route('emails.send', $email) }}" class="d-inline">@csrf<button class="btn btn-sm btn-light">Send now</button></form>
                            </div>
                        @endif
                        <details class="small text-muted mt-1"><summary>Preview</summary><div class="bg-light p-2 rounded mt-1">{{ $email->body }}</div></details>
                    </div>
                @empty
                    <p class="text-muted small py-2 mb-0">No emails yet.@if(!$lead->email) Add an email to the lead to enable outreach.@endif</p>
                @endforelse
            </div>
        </div>
        <div class="card shadow-sm mb-3" id="followups">
            <div class="card-header bg-white fw-bold">Follow-ups</div>
            <div class="card-body py-2">
                @forelse($lead->followUps as $fu)
                    <div class="border-bottom py-2 small d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Follow-up #{{ $fu->sequence_number }}</div>
                            <div class="text-muted">Scheduled {{ $fu->scheduled_at->format('d M Y H:i') }}</div>
                        </div>
                        <span class="badge {{ $fu->status==='pending'?'text-bg-warning':'bg-light text-dark border' }}">{{ $fu->status }}</span>
                    </div>
                @empty
                    <p class="text-muted small py-2 mb-0">No follow-ups scheduled yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
