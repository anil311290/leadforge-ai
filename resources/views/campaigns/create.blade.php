@extends('layouts.app')
@section('title', 'Find Projects')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <!-- Header -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary" style="font-size:1.6rem;width:52px;height:52px;">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Find Projects</h4>
                        <p class="text-muted small mb-0">Discover businesses in an area, scan their websites, and qualify them for your services.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('campaigns.store') }}">
                    @csrf

                    <!-- Location -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            <i class="bi bi-geo-alt text-primary"></i> Locations/Target Area <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="location" class="form-control" required
                               placeholder="e.g. Mumbai, Pune, Bengaluru (SMEs &amp; Growing Companies)"
                               value="{{ old('location') }}">
                        <div class="form-text mt-1">
                            <i class="bi bi-info-circle me-1"></i>The geographic area or market segment you want to prospect.
                        </div>
                    </div>

                    <!-- Options grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                <i class="bi bi-tag text-secondary"></i> Campaign name
                            </label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Optional — default is based on location" value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                <i class="bi bi-broadcast text-secondary"></i> Radius (km)
                            </label>
                            <input type="number" step="any" min="0" name="radius_km" class="form-control"
                                   placeholder="Optional" value="{{ old('radius_km') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                <i class="bi bi-filter-circle text-secondary"></i> Minimum quality score
                            </label>
                            <input type="number" min="0" max="100" name="min_score" class="form-control"
                                   value="{{ old('min_score', 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                <i class="bi bi-building text-secondary"></i> Max businesses to target
                            </label>
                            <input type="number" min="1" max="500" name="max_businesses" class="form-control"
                                   placeholder="Optional" value="{{ old('max_businesses') }}">
                        </div>
                    </div>

                    <!-- Automation -->
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1 mb-3">
                            <i class="bi bi-gear-wide-connected text-primary"></i> What should be automated?
                        </label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="auto_analysis_enabled" id="auto" value="1"
                                   @checked((bool) old('auto_analysis_enabled', true))>
                            <label class="form-check-label fw-medium" for="auto">
                                <i class="bi bi-motherboard me-1 text-info"></i> Auto scan &amp; analyse websites
                            </label>
                            <div class="small text-muted ms-4 ps-1">Discover the right project, value &amp; opportunities automatically.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="email_outreach_enabled" id="email" value="1"
                                   @checked((bool) old('email_outreach_enabled'))>
                            <label class="form-check-label fw-medium" for="email">
                                <i class="bi bi-envelope-paper me-1 text-warning"></i> Auto-generate personalised outreach emails
                            </label>
                            <div class="small text-muted ms-4 ps-1">Drafts personalised emails for you to review before sending.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2 py-3" id="startBtn">
                        <i class="bi bi-rocket-takeoff fs-5"></i>
                        <span class="fw-semibold">Start Discovery</span>
                    </button>
                    <div class="text-center small text-muted mt-3">
                        <i class="bi bi-shield-check me-1 text-success"></i>Compliant, human-reviewed sources only.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection