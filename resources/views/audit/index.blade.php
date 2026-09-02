@extends('layouts.app')
@section('title', 'Audit Trail')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-primary"></i> Audit Trail
        </h4>
        <p class="text-muted small mb-0">Every important action in the system, logged for transparency.</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search actions..." value="{{ request('q') }}" style="max-width:200px;">
        </div>
        <select name="entity" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="">All entities</option>
            @foreach(['Lead', 'Campaign', 'EmailMessage', 'FollowUp', 'Setting', 'User'] as $e)
                <option value="{{ $e }}" @selected(request('entity') === $e)>{{ $e }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-primary px-3"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if(request('q') || request('entity'))
            <a href="{{ route('audit.index') }}" class="btn btn-sm btn-light px-3"><i class="bi bi-x-lg me-1"></i>Clear</a>
        @endif
    </form>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <span class="fw-semibold small text-muted">
            <i class="bi bi-list-check me-1"></i>{{ $logs->total() }} total records
        </span>
        <span class="badge bg-light text-muted border">{{ $logs->currentPage() }}/{{ $logs->lastPage() }} pages</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3"><i class="bi bi-clock me-1"></i>Time</th>
                    <th><i class="bi bi-person me-1"></i>User</th>
                    <th><i class="bi bi-box me-1"></i>Entity</th>
                    <th><i class="bi bi-lightning me-1"></i>Action</th>
                    <th>ID</th>
                    <th class="pe-3"><i class="bi bi-info-circle me-1"></i>Details</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                @php
                    $actionIcons = [
                        'campaign_started' => ['bi-play-circle', 'success'],
                        'campaign_completed' => ['bi-check-circle', 'success'],
                        'campaign_failed' => ['bi-x-circle', 'danger'],
                        'campaign_paused' => ['bi-pause-circle', 'warning'],
                        'campaign_resumed' => ['bi-arrow-repeat', 'info'],
                        'campaign_cancelled' => ['bi-stop-circle', 'secondary'],
                        'lead_created' => ['bi-plus-circle', 'primary'],
                        'lead_discovered' => ['bi-building', 'info'],
                        'lead_status_changed' => ['bi-arrow-left-right', 'warning'],
                        'lead_deleted' => ['bi-trash', 'danger'],
                        'leads_imported' => ['bi-upload', 'primary'],
                        'analysis_completed' => ['bi-motherboard', 'success'],
                        'email_generated' => ['bi-envelope-paper', 'primary'],
                        'email_approved' => ['bi-check2', 'success'],
                        'email_sent' => ['bi-send-check', 'success'],
                        'followup_triggered' => ['bi-alarm', 'warning'],
                        'followup_completed' => ['bi-check-circle', 'success'],
                        'pipeline_moved' => ['bi-kanban', 'info'],
                        'settings_updated' => ['bi-gear', 'secondary'],
                    ];
                    $icon = $actionIcons[$log->action] ?? ['bi-record-circle', 'secondary'];
                @endphp
                <tr class="small align-middle">
                    <td class="text-muted ps-3" style="white-space:nowrap;">
                        <span title="{{ $log->created_at->format('d M Y H:i:s') }}">{{ $log->created_at->diffForHumans() }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $log->user->name ?? 'system' }}</span>
                        @if($log->user)<div class="text-muted" style="font-size:11px;">{{ $log->user->email }}</div>@endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1">{{ $log->entity }}</span>
                    </td>
                    <td>
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi {{ $icon[0] }} text-{{ $icon[1] }}"></i>
                            <span class="fw-semibold">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span>
                        </span>
                    </td>
                    <td>
                        @if($log->entity_id)
                            <code class="small">{{ $log->entity_id }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="pe-3" style="max-width:250px;">
                        @php
                            $before = $log->before ?? [];
                            $after = $log->after ?? [];
                            $detailParts = [];
                            if (is_array($before) && !empty($before)) {
                                foreach ($before as $k => $v) { $detailParts[] = "$k: $v"; }
                            }
                            if (is_array($after) && !empty($after)) {
                                foreach ($after as $k => $v) { $detailParts[] = "$k → $v"; }
                            }
                            $detailStr = implode(', ', $detailParts);
                        @endphp
                        @if($detailStr)
                            <span class="text-muted small text-truncate d-inline-block" style="max-width:220px;" title="{{ $detailStr }}">{{ Str::limit($detailStr, 50) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state py-5">
                            <i class="bi bi-shield-check" style="font-size:2.5rem;color:#c2cad6;"></i>
                            <h6 class="fw-bold mt-3">No audit records yet</h6>
                            <p class="text-muted small mb-0">Actions will appear here as you use the system.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer border-top-0 py-3 bg-white">
            <nav aria-label="Audit log pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                    @if ($logs->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link border rounded px-3 py-1 small text-muted bg-light"><i class="bi bi-chevron-left"></i> Previous</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link border rounded px-3 py-1 small text-dark bg-white" href="{{ $logs->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i> Previous</a>
                        </li>
                    @endif

                    @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                        <li class="page-item {{ $page === $logs->currentPage() ? 'active' : '' }}">
                            <a class="page-link border rounded px-3 py-1 small {{ $page === $logs->currentPage() ? 'bg-primary text-white border-primary' : 'bg-white text-dark' }}" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($logs->hasMorePages())
                        <li class="page-item">
                            <a class="page-link border rounded px-3 py-1 small text-dark bg-white" href="{{ $logs->nextPageUrl() }}" rel="next">Next <i class="bi bi-chevron-right"></i></a>
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
