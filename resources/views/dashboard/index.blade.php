@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <p class="text-muted small mb-0">Sales intelligence overview · {{ now()->format('d M Y') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ number_format($stats['total_leads']) }}</div>
                    <div class="text-muted small">Total Leads</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-fire"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['hot_leads'] }}</div>
                    <div class="text-muted small">Hot Leads</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-lightning-charge"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['high_leads'] }}</div>
                    <div class="text-muted small">High Potential</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-envelope-check"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['replies_received'] }}</div>
                    <div class="text-muted small">Email Replies</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['follow_ups_due_today'] }}</div>
                    <div class="text-muted small">Follow-ups Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['meetings'] }}</div>
                    <div class="text-muted small">Meetings</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-trophy"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['won'] }}</div>
                    <div class="text-muted small">Won Projects</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card p-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="fs-5 fw-bold">{{ $stats['lost'] }}</div>
                    <div class="text-muted small">Lost Projects</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card p-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Top Opportunities</h6>
                <a href="{{ route('opportunities.index') }}" class="small">View all</a>
            </div>
            @if($topOpportunities->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr><th>Company</th><th>Industry</th><th>Service</th><th>Score</th><th>Est. Value</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @foreach($topOpportunities as $lead)
                            <tr>
                                <td><a href="{{ route('leads.show', $lead) }}">{{ Str::limit($lead->company, 22) }}</a><div class="small text-muted">{{ $lead->location }}</div></td>
                                <td class="small">{{ $lead->industry ?? '—' }}</td>
                                <td class="small">{{ $lead->recommended_service ?? '—' }}</td>
                                <td><span class="badge {{ $lead->score_class ? 'badge-'.strtolower($lead->score_class) : 'badge-ignore' }}">{{ $lead->opportunity_score }}</span></td>
                                <td class="small fw-semibold">{{ $lead->estimated_max ? '₹'.number_format($lead->estimated_max) : '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $lead->status }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-lightning-charge"></i>
                    <p class="mt-2">No analysed opportunities yet.<br>Start by running a <a href="{{ route('campaigns.create') }}">discovery campaign</a>.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card p-3 shadow-sm mb-3">
            <h6 class="fw-bold mb-2">Pipeline Value</h6>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="fs-3 fw-bold">₹{{ number_format($stats['pipeline_value']) }}</div>
                <div class="badge bg-success">Win rate {{ $stats['win_rate'] }}%</div>
            </div>
            <div style="position:relative; height:150px;">
                <canvas id="pipelineChart"></canvas>
            </div>
        </div>
        <div class="card p-3 shadow-sm">
            <h6 class="fw-bold mb-2">Recent Activity</h6>
            <ul class="list-unstyled mb-0">
            @forelse($recentActivities as $activity)
                <li class="d-flex align-items-start gap-2 py-1 border-bottom">
                    <i class="bi bi-circle-fill mt-1 text-muted" style="font-size:.5rem"></i>
                    <div class="small">
                        <strong>{{ $activity->title ?? ucfirst(str_replace('_',' ',$activity->type)) }}</strong>
                        <div class="text-muted">
                            @if($activity->lead) <a href="{{ route('leads.show', $activity->lead) }}" class="text-muted">{{ $activity->lead->company }}</a> · @endif
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-muted small">No recent activity.</li>
            @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const pipelineData = @json($pipelineBreakdown);
new Chart(document.getElementById('pipelineChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(pipelineData),
        datasets: [{ label: 'Leads', data: Object.values(pipelineData), backgroundColor: '#1e6fd9', borderRadius: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endsection