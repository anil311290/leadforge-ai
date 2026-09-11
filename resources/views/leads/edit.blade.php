@extends('layouts.app')
@section('title', 'Edit Lead')

@section('content')
@php
    $googleSearchUrl = 'https://www.google.com/search?q='.urlencode(trim($lead->company.' '.$lead->city.' '.$lead->location));
@endphp
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <a href="{{ route('leads.show', $lead) }}" class="small text-muted">&larr; Back to lead</a>
                        <h4 class="fw-bold mb-1">Edit Lead</h4>
                        <p class="text-muted small mb-0">Update details after manual verification.</p>
                    </div>
                    <a href="{{ $googleSearchUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-google me-1"></i>Search on Google
                    </a>
                </div>
                <hr>
                <form method="POST" action="{{ route('leads.update', $lead) }}">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Company name <span class="text-danger">*</span></label>
                            <input type="text" name="company" class="form-control" required value="{{ old('company', $lead->company) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="text" name="website" class="form-control" placeholder="example.com" value="{{ old('website', $lead->website) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Industry</label>
                            <input type="text" name="industry" class="form-control" value="{{ old('industry', $lead->industry) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $lead->location) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Recommended service</label>
                            <input type="text" name="recommended_service" class="form-control" value="{{ old('recommended_service', $lead->recommended_service) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="4">{{ old('notes', $lead->notes) }}</textarea>
                        </div>
                    </div>
                    <hr class="my-4">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Lead</button>
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
@endsection