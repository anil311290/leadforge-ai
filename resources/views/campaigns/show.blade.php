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
            <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf<button class="btn btn-outline-warning btn-sm"><i class="bi bi-pause me-1"></i>Pause</button></form>
        @elseif($campaign->status === 'paused')
            <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf<button class="btn btn-success btn-sm"><i class="bi bi-play me-1"></i>Resume</button></form>
        @endif
        @if(in_array($campaign->status, ['running','paused']))
            <form method="POST" action="{{ route('campaigns.cancel', $campaign) }}">@csrf<button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg me-1"></i>Cancel</button></form>
        @endif
    </div>
</div>

@if($campaign->error)
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $campaign->error }}</div>
@endif

{{-- Live progress tracker --}}
<div class="card shadow-sm border-0 mb-4" id="progressCard" data-campaign-id="{{ $campaign->id }}" data-initial-status="{{ $campaign->status }}">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <span id="progressIcon"><i class="bi bi-arrow-repeat text-primary"></i></span>
                <span id="progressStatus">Campaign progress</span>
            </h6>
            <span class="badge rounded-pill fs-6 px-3 py-1" id="progressPercent">{{ $campaign->progress ?? 0 }}%</span>
        </div>

        <div class="progress mb-3" style="height:12px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar"
                 role="progressbar" style="width:{{ $campaign->progress ?? 0 }}%"></div>
        </div>

        <p class="text-muted small mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-gear text-secondary"></i>
            <span id="progressMessage">{{ $campaign->progress_message ?? 'Waiting to start…' }}</span>
        </p>

        {{-- Pipeline stages --}}
        <div class="row g-2 text-center mb-3" id="stagesRow">
            <div class="col-3">
                <div class="d-flex flex-column align-items-center gap-1">
                    <div class="stage-dot d-flex align-items-center justify-content-center" id="stage-discover"><i class="bi bi-building"></i></div>
                    <span class="small fw-medium">Discover</span>
                </div>
            </div>
            <div class="col-3">
                <div class="d-flex flex-column align-items-center gap-1">
                    <div class="stage-dot d-flex align-items-center justify-content-center" id="stage-scan"><i class="bi bi-search"></i></div>
                    <span class="small fw-medium">Scan</span>
                </div>
            </div>
            <div class="col-3">
                <div class="d-flex flex-column align-items-center gap-1">
                    <div class="stage-dot d-flex align-items-center justify-content-center" id="stage-analyse"><i class="bi bi-motherboard"></i></div>
                    <span class="small fw-medium">Analyse</span>
                </div>
            </div>
            <div class="col-3">
                <div class="d-flex flex-column align-items-center gap-1">
                    <div class="stage-dot d-flex align-items-center justify-content-center" id="stage-done"><i class="bi bi-check-lg"></i></div>
                    <span class="small fw-medium">Complete</span>
                </div>
            </div>
        </div>

        {{-- Live numbers --}}
        <div class="row g-2">
            <div class="col-4">
                <div class="bg-light rounded-3 p-2 text-center">
                    <div class="fw-bold" id="liveTotal">{{ $stats['businesses_discovered'] }}</div>
                    <div class="text-muted small">Discovered</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light rounded-3 p-2 text-center">
                    <div class="fw-bold" id="liveScanned">{{ $stats['websites_scanned'] }}</div>
                    <div class="text-muted small">Scanned</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light rounded-3 p-2 text-center">
                    <div class="fw-bold" id="liveAnalysed">{{ $stats['analysed'] }}</div>
                    <div class="text-muted small">Analysed</div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-1 text-primary"></i>Leads in this campaign</span>
        <span class="badge bg-light text-muted border">{{ $leads->total() }}</span>
    </div>
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

@section('scripts')
<style>
    .stage-dot{width:44px;height:44px;border-radius:50%;background:#eef1f6;color:#a3adbf;font-size:1.1rem;transition:all .3s}
    .stage-dot.active{background:#1e6fd9;color:#fff;box-shadow:0 0 0 4px rgba(30,111,217,.15)}
    .stage-dot.done{background:#10b981;color:#fff}
    #progressBar{transition:width .6s ease}
</style>
<script>
(function(){
    const card = document.getElementById('progressCard');
    if (!card) return;
    const campaignId = card.dataset.campaignId;
    const url = "{{ route('campaigns.progress', $campaign) }}";

    const bar = document.getElementById('progressBar');
    const pct = document.getElementById('progressPercent');
    const msg = document.getElementById('progressMessage');
    const statusEl = document.getElementById('progressStatus');
    const iconEl = document.getElementById('progressIcon');
    const liveTotal = document.getElementById('liveTotal');
    const liveScanned = document.getElementById('liveScanned');
    const liveAnalysed = document.getElementById('liveAnalysed');

    function setStage(name, state){
        const el = document.getElementById('stage-' + name);
        if (!el) return;
        el.classList.remove('active','done');
        if (state) el.classList.add(state);
    }

    function update(data){
        bar.style.width = data.progress + '%';
        pct.textContent = data.progress + '%';

        if (data.message) msg.textContent = data.message;

        const status = data.status;
        let label = 'Campaign progress';
        let icon = '<i class="bi bi-arrow-repeat text-primary"></i>';
        let barClass = 'bg-primary';

        if (status === 'running') {
            label = 'Running…';
            icon = '<i class="bi bi-arrow-repeat text-primary"></i>';
            barClass = 'bg-primary';
        } else if (status === 'paused') {
            label = 'Paused';
            icon = '<i class="bi bi-pause-circle text-warning"></i>';
            barClass = 'bg-warning';
        } else if (status === 'completed') {
            label = 'Completed';
            icon = '<i class="bi bi-check-circle-fill text-success"></i>';
            barClass = 'bg-success';
        } else if (status === 'failed') {
            label = 'Failed';
            icon = '<i class="bi bi-x-circle-fill text-danger"></i>';
            barClass = 'bg-danger';
        }

        statusEl.textContent = label;
        iconEl.innerHTML = icon;
        bar.className = 'progress-bar progress-bar-striped progress-bar-animated ' + barClass;

        // Stages
        const p = data.progress;
        setStage('discover', p > 5 ? 'done' : (p > 0 ? 'active' : null));
        setStage('scan', p >= 55 ? (p > 80 ? 'done' : 'active') : null);
        setStage('analyse', p >= 80 ? (p >= 100 ? 'done' : 'active') : null);
        setStage('done', p >= 100 ? 'done' : null);

        // Live numbers
        if (data.leads) {
            liveTotal.textContent = data.leads.total;
            liveScanned.textContent = data.leads.scanned;
            liveAnalysed.textContent = data.leads.analysed;
        }

        // Stop polling once finished
        if (['completed','failed','cancelled'].includes(status)) {
            clearInterval(timer);
            if (status === 'completed') {
                // Refresh page once to show final data
                setTimeout(() => window.location.reload(), 1500);
            }
        }
    }

    async function poll(){
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            update(data);
        } catch (e) {
            // ignore transient errors
        }
    }

    let timer = null;
    const initialStatus = card.dataset.initialStatus;
    if (['running','paused'].includes(initialStatus)) {
        timer = setInterval(poll, 2000);
    } else {
        // Still render final state once
        poll();
    }
})();
</script>
@endsection