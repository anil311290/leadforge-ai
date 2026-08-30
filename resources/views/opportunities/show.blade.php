@extends('layouts.app')
@section('title', 'Opportunity — '.$lead->company)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('opportunities.index') }}" class="small text-muted">&larr; Opportunities</a>
        <h4 class="fw-bold mb-0">{{ $lead->company }}
            @if($lead->score_class)<span class="badge {{ 'badge-'.strtolower($lead->score_class) }}">{{ $lead->score_class }}</span>@endif
        </h4>
    </div>
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-light btn-sm"><i class="bi bi-person me-1"></i> Full lead</a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Recommended Opportunities</div>
            <div class="card-body">
                @forelse($lead->recommendations as $rec)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <div class="fw-semibold">{{ $rec->service_name }}</div>
                            <div class="d-flex gap-2 align-items-center"><span class="score-pill">{{ $rec->score }}<small>/100</small></span>
                            <span class="badge bg-light text-dark border">Conf {{ $rec->confidence }}%</span></div>
                        </div>
                        <div class="small text-muted mt-1">Value: ₹{{ number_format($rec->estimated_min) }} – ₹{{ number_format($rec->estimated_max) }}</div>
                        @if($rec->inference)<div class="small mt-1"><strong>Why:</strong> {{ $rec->inference }}</div>@endif
                        @if($rec->evidence)
                            <div class="small mt-1"><strong>Evidence:</strong>
                                @foreach(array_slice((array)$rec->evidence,0,5) as $ev)<span class="badge bg-light text-muted border me-1">{{ $ev }}</span>@endforeach
                            </div>
                        @endif
                        @if($rec->recommendation)<div class="small mt-1 text-accent"><i class="bi bi-lightbulb"></i> {{ $rec->recommendation }}</div>@endif
                    </div>
                @empty
                    <div class="empty-state"><i class="bi bi-inbox"></i><p class="mt-2">No recommendations recorded.</p></div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Snapshot</div>
            <div class="card-body small">
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Score</span><strong>{{ $lead->opportunity_score ?? '—' }} / 100</strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Confidence</span><strong>{{ $lead->confidence ?? '—' }}%</strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Data quality</span><strong>{{ $lead->data_quality ?? '—' }}/100</strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Industry</span><strong>{{ $lead->industry ?? '—' }}</strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Website</span><strong class="text-truncate">{{ $lead->normalized_domain ?? '—' }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span class="text-muted">Status</span><strong>{{ $lead->status }}</strong></div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Marketing Copy</div>
            <div class="card-body small">
                <p class="mb-1">Use this angle in the outreach email:</p>
                <blockquote class="border-start ps-3 text-muted">{{ $lead->analysis['summary'] ?? 'No generated summary yet.' }}</blockquote>
                <a href="{{ route('emails.generate', $lead) }}" class="btn btn-sm btn-primary" onclick="event.preventDefault();document.getElementById('gen').submit();"><i class="bi bi-magic me-1"></i>Generate email draft</a>
                <form id="gen" method="POST" action="{{ route('emails.generate', $lead) }}" class="d-none">@csrf</form>
            </div>
        </div>
    </div>
</div>
@endsection
