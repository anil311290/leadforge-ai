@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">My Profile</h4>
                <p class="text-muted small">Update your name, email and password.</p>
                <hr>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="mb-3"><label class="form-label fw-semibold">Full name</label><input type="text" name="name" class="form-control" required value="{{ auth()->user()->name }}"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" required value="{{ auth()->user()->email }}"></div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">New password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Confirm password</label><input type="password" name="password_confirmation" class="form-control"></div>
                    </div>
                    <hr>
                    <button class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
