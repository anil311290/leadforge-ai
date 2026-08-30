@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Reports</h4><p class="text-muted small mb-0">Pipeline health, value and engagement overview.</p></div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="date" name="from" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($start)->format('Y-m-d') }}">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($end)->format('Y-m-d') }}">
        <button class="btn btn-sm btn-primary">Apply</button>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">{{ number_format($kpis['discovered']) }}</div><div class="text-muted small">Discovered</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold">{{ $kpis['qualified'] }}</div><div class="text-muted small">Qualified</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-success">{{ $kpis['emails_sent'] }}</div><div class="text-muted small">Emails sent</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-info">{{ $kpis['replies'] }}</div><div class="text-muted small">Replies</div></div></div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Conversion Funnel</div>
            <div class="card-body py-3">
                @foreach($funnel as $stage => $count)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1"><span>{{ $stage }}</span><span class="fw-semibold">{{ $count }}</span></div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-primary" style="width: {{ $funnel['Discovered'] > 0 ? ($count / $funnel['Discovered']) * 100 : 0 }}%"></div></div>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between fw-bold"><span>Won</span><span class="text-emerald">₹{{ number_format($kpis['revenue']) }}</span></div>
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Top Services Recommended</div>
            <div class="card-body py-2">
                @forelse($byService as $row)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span class="small fw-semibold">{{ $row->recommended_service }}</span>
                        <span class="badge bg-light text-dark border">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-muted small py-2 mb-0">No recommendations yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Emails over time</div>
            <div class="card-body"><canvas id="emailChart" style="max-height:220px"></canvas></div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Leads by score class</div>
            <div class="card-body py-2">
                <div class="d-flex gap-3">
                    @foreach($byScoreClass as $row)
                        <div class="border rounded p-3 text-center flex-fill"><div class="fs-5 fw-bold">{{ $row->total }}</div><div class="small text-muted">{{ $row->score_class }}</div></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const dayLabels = @json($emailsOverTime->pluck('day'));
const dayTotals = @json($emailsOverTime->pluck('total'));
new Chart(document.getElementById('emailChart'), {
    type: 'line',
    data: { labels: dayLabels, datasets: [{ label: 'Emails', data: dayTotals, borderColor: '#2f86f6', backgroundColor: 'rgba(47,134,246,.15)', fill: true, tension: .3 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endsection
