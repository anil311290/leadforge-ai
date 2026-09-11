@extends('layouts.app')
@section('title', 'Add Freelancer Account')

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-1">Add Freelancer.com Account</h4>
        <p class="text-muted small">Each account bids independently using its own OAuth key and rules.</p>
        <hr>
        <form method="POST" action="{{ route('freelancer.accounts.store') }}">
            @csrf
            @include('freelancer.accounts._form', ['account' => null])
            <hr>
            <button class="btn btn-primary">Save Account</button>
            <a href="{{ route('freelancer.accounts.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </div>
</div>
@endsection
