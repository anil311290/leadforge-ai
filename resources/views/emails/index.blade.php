@extends('layouts.app')
@section('title', 'Emails')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Email Outreach</h4><p class="text-muted small mb-0">Draft, approve &amp; send personalised outreach.</p></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-warning">{{ $summary['pending_approval'] }}</div><div class="text-muted small">Pending approval</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-info">{{ $summary['approved_pending'] }}</div><div class="text-muted small">Approved</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-success">{{ $summary['sent'] }}</div><div class="text-muted small">Sent</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 shadow-sm text-center"><div class="fs-4 fw-bold text-primary">{{ $summary['replied'] }}</div><div class="text-muted small">Replies</div></div></div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Lead</th><th>Subject</th><th>Direction</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
            @forelse($messages as $msg)
                <tr>
                    <td>@if($msg->lead)<a href="{{ route('leads.show', $msg->lead) }}" class="fw-semibold">{{ Str::limit($msg->lead->company, 26) }}</a>@else<span class="text-muted">—</span>@endif</td>
                    <td class="small">{{ Str::limit($msg->subject, 42) }}</td>
                    <td><span class="badge {{ $msg->direction==='inbound'?'text-bg-info':'bg-light text-dark border' }}">{{ $msg->direction }}</span></td>
                    <td><span class="badge {{ $msg->status==='sent'?'text-bg-success':($msg->status==='pending_approval'?'text-bg-warning':($msg->status==='approved'?'text-bg-info':'bg-light text-dark border')) }}">{{ $msg->status }}</span></td>
                    <td class="small text-muted">{{ $msg->created_at->diffForHumans() }}</td>
                    <td>
                        @if(in_array($msg->status, ['pending_approval','approved']))
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('emails.approve', $msg) }}">@csrf<button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button></form>
                                <form method="POST" action="{{ route('emails.send', $msg) }}">@csrf<button class="btn btn-sm btn-primary"><i class="bi bi-send"></i></button></form>
                            </div>
                        @else
                            <details class="small"><summary>Preview</summary><div class="bg-light p-2 rounded mt-1">{{ $msg->body }}</div></details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-envelope-open"></i><h6 class="mt-2 fw-bold">No emails yet</h6><p>Generate drafts from a <a href="{{ route('leads.index') }}">lead</a> to begin.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())<div class="card-footer">{{ $messages->links() }}</div>@endif
</div>
@endsection
