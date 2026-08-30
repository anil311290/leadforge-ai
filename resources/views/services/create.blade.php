@extends('layouts.app')
@section('title', 'Add Service')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Add a Service</h4>
                <p class="text-muted small">Define the service, its typical project value, and (optionally) detection rules.</p>
                <hr>
                <form method="POST" action="{{ route('services.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8"><label class="form-label fw-semibold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="{{ old('name') }}"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Web, CRM" value="{{ old('category') }}"></div>
                        <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Min project value (₹)</label><input type="number" min="0" name="min_value" class="form-control" value="{{ old('min_value', 50000) }}"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Max project value (₹)</label><input type="number" min="0" name="max_value" class="form-control" value="{{ old('max_value', 200000) }}"></div>
                        <div class="col-12">
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Active for discovery matching</label></div>
                        </div>
                    </div>
                    <hr>
                    <button class="btn btn-primary">Save Service</button>
                    <a href="{{ route('services.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
