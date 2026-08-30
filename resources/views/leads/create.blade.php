@extends('layouts.app')
@section('title', 'New Lead')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Add a Lead</h4>
                <p class="text-muted small">Add a business manually. Providing a website enables scanning &amp; analysis.</p>
                <hr>
                <form method="POST" action="{{ route('leads.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Company name <span class="text-danger">*</span></label>
                            <input type="text" name="company" class="form-control" required value="{{ old('company') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="text" name="website" class="form-control" placeholder="example.com" value="{{ old('website') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Industry</label>
                            <input type="text" name="industry" class="form-control" value="{{ old('industry') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                    </div>
                    <hr class="my-4">
                    <button class="btn btn-primary">Save Lead</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection