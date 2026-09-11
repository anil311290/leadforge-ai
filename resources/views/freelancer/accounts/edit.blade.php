@extends('layouts.app')
@section('title', 'Edit Freelancer Account')

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-1">Edit Freelancer.com Account</h4>
        <p class="text-muted small">Update bidding rules or rotate the OAuth key for {{ $account->name }}.</p>
        @if($account->status === 'error' && $account->last_error)
            <div class="alert alert-danger small">{{ $account->last_error }}</div>
        @endif
        <hr>
        <form method="POST" action="{{ route('freelancer.accounts.update', $account) }}">
            @csrf
            @method('PATCH')
            @include('freelancer.accounts._form', ['account' => $account])
            <hr>
            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('freelancer.accounts.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
