@extends('layouts.app')

@section('title', 'Edit Website')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Website</h4>
        <p class="text-muted small mb-0">Update the brand and details for {{ $businessWebsite->business_name }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('website-builder.websites.preview', $businessWebsite) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i>Preview</a>
        @if($businessWebsite->status !== 'published')
            <form method="POST" action="{{ route('website-builder.websites.publish', $businessWebsite) }}">@csrf<button class="btn btn-success btn-sm" type="submit"><i class="bi bi-globe2 me-1"></i>Publish</button></form>
        @endif
    </div>
</div>

<div class="card shadow-sm border-0 p-4">
    <form method="POST" action="{{ route('website-builder.websites.update', $businessWebsite) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Business Name</label>
                <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $businessWebsite->business_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Owner Name</label>
                <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $businessWebsite->owner_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $businessWebsite->phone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $businessWebsite->email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $businessWebsite->city) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $businessWebsite->state) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $businessWebsite->address) }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">Business Description</label>
                <textarea name="business_description" class="form-control" rows="4">{{ old('business_description', $businessWebsite->business_description) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $businessWebsite->slug) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="draft" {{ $businessWebsite->status === 'draft' ? 'selected' : '' }}>draft</option>
                    <option value="published" {{ $businessWebsite->status === 'published' ? 'selected' : '' }}>published</option>
                    <option value="unpublished" {{ $businessWebsite->status === 'unpublished' ? 'selected' : '' }}>unpublished</option>
                    <option value="archived" {{ $businessWebsite->status === 'archived' ? 'selected' : '' }}>archived</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Website template</label>
                <select name="template_id" class="form-select">
                    @foreach(\App\Models\WebsiteTemplate::where('is_active', true)->orderBy('name')->get() as $template)
                        <option value="{{ $template->id }}" @selected((int) $businessWebsite->template_id === $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Primary Color</label>
                <input type="text" name="primary_color" class="form-control" value="{{ old('primary_color', $brandPalette['primary']) }}" placeholder="#1769aa">
            </div>
            <div class="col-md-4">
                <label class="form-label">Secondary Color</label>
                <input type="text" name="secondary_color" class="form-control" value="{{ old('secondary_color', $brandPalette['secondary']) }}" placeholder="#102a43">
            </div>
            <div class="col-md-4">
                <label class="form-label">Accent Color</label>
                <input type="text" name="accent_color" class="form-control" value="{{ old('accent_color', $brandPalette['accent']) }}" placeholder="#f0a202">
            </div>
            <div class="col-md-6">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                @if($businessWebsite->logo)
                    <img src="{{ asset('storage/'.$businessWebsite->logo) }}" alt="Logo" class="mt-2" style="max-width: 180px; max-height: 80px;">
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label">Favicon</label>
                <input type="file" name="favicon" class="form-control" accept=".png,.ico">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('website-builder.websites.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('website-builder.media.index', $businessWebsite) }}" class="btn btn-outline-primary">Manage Gallery</a>
        </div>
    </form>
</div>
@endsection
