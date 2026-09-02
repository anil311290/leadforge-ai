@extends('layouts.app')
@section('title', 'Pending Approval')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="fw-bold mb-1">Pending Approval</h4><p class="text-muted small mb-0">Review AI-drafted emails before they go out.</p></div>
    <a href="{{ route('emails.index') }}" class="btn btn-light btn-sm">All emails</a>
</div>

@forelse($messages as $msg)
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">@if($msg->lead){{ $msg->lead->company }}@endif · <span class="text-muted">{{ $msg->subject }}</span></div>
            <span class="badge {{ $msg->status==='approved'?'text-bg-info':'text-bg-warning' }}">{{ $msg->status }}</span>
        </div>
        <div class="small text-muted mb-2">
            <i class="bi bi-envelope me-1"></i><strong>To:</strong> {{ $msg->to_email ?: '—' }}
            @if($msg->from_email) · <strong>From:</strong> {{ $msg->from_email }}@endif
        </div>
        <div class="bg-light rounded p-3 small white-space:pre-wrap;">{{ $msg->body }}</div>
        <div class="mt-3 d-flex gap-2">
            <form method="POST" action="{{ route('emails.approve', $msg) }}">@csrf<button class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button></form>
            <form method="POST" action="{{ route('emails.send', $msg) }}">@csrf<button class="btn btn-sm btn-primary"><i class="bi bi-send me-1"></i>Send now</button></form>
        </div>
    </div>
</div>
@empty
<div class="card shadow-sm"><div class="empty-state"><i class="bi bi-check2-all"></i><h6 class="mt-2 fw-bold">All caught up</h6><p>No emails waiting for approval.</p></div></div>
@endforelse

@if($messages->hasPages())<div>{{ $messages->links() }}</div>@endif
@endsection
