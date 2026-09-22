@extends('layouts.app')

@section('title', 'Create Website')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="text-primary small fw-semibold mb-1"><i class="bi bi-magic me-1"></i>Website Builder</div>
        <h4 class="fw-bold mb-1">Create a new website</h4>
        <p class="text-muted small mb-0">Start with the business basics. Branding, content and publishing come next.</p>
    </div>
    <a href="{{ route('website-builder.websites.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to websites</a>
</div>

@if($lead)
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-link-45deg fs-5"></i>
        <div><strong>Lead connected:</strong> {{ $lead->company }}. We prefilled the available business details below.</div>
    </div>
@endif

<div class="card shadow-sm border-0 website-builder-form">
    <form method="POST" action="{{ route('website-builder.websites.store') }}">
        @csrf
        <input type="hidden" name="lead_id" value="{{ old('lead_id', $lead?->id) }}">
        <div class="p-4 border-bottom">
            <div class="d-flex align-items-start gap-3 mb-3">
                <span class="rounded-circle bg-primary-subtle text-primary px-2 py-1"><i class="bi bi-building"></i></span>
                <div><h5 class="mb-1">Business identity</h5><p class="section-copy mb-0">The name and description visitors will see first.</p></div>
            </div>
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold">Business name <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control form-control-lg" value="{{ old('business_name', $lead?->company) }}" placeholder="e.g. Rama Medical Store" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Owner or contact name</label>
                    <input type="text" name="owner_name" class="form-control form-control-lg" value="{{ old('owner_name', $lead?->contact_name ?? '') }}" placeholder="Optional">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Business description</label>
                    <textarea name="business_description" class="form-control" rows="3" placeholder="What does this business offer? You can generate polished copy with AI after creating the draft.">{{ old('business_description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="p-4 border-bottom">
            <div class="d-flex align-items-start gap-3 mb-3">
                <span class="rounded-circle bg-success-subtle text-success px-2 py-1"><i class="bi bi-telephone"></i></span>
                <div><h5 class="mb-1">Contact and location</h5><p class="section-copy mb-0">These details power the contact section on the public website.</p></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $lead?->phone) }}" placeholder="+91 98765 43210"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $lead?->email) }}" placeholder="hello@business.com"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $lead?->city) }}" placeholder="Jaipur"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">State</label><input type="text" name="state" class="form-control" value="{{ old('state', $lead?->state) }}" placeholder="Rajasthan"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Country</label><input type="text" name="country" class="form-control" value="{{ old('country', $lead?->country ?? 'India') }}" placeholder="India"></div>
                <div class="col-12"><label class="form-label fw-semibold">Address</label><textarea name="address" class="form-control" rows="2" placeholder="Street, area, landmark">{{ old('address', $lead?->address) }}</textarea></div>
            </div>
        </div>

        <div class="p-4">
            <div class="d-flex align-items-start gap-3 mb-3">
                <span class="rounded-circle bg-warning-subtle text-warning px-2 py-1"><i class="bi bi-palette"></i></span>
                <div><h5 class="mb-1">Choose a starting style</h5><p class="section-copy mb-0">You can change the template and branding later.</p></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Template</label>
                    <select name="template_id" class="form-select form-select-lg">
                        @foreach(\App\Models\WebsiteTemplate::where('is_active', true)->orderBy('name')->get() as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-note border rounded p-3 w-100"><i class="bi bi-link-45deg me-1"></i>Public URL slug is generated automatically from the business name.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle me-1"></i>Create website draft</button>
                <a href="{{ route('website-builder.websites.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
