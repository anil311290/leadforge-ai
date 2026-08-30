@extends('layouts.app')
@section('title', 'Find Projects')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary" style="font-size:1.6rem;"><i class="bi bi-bullseye"></i></div>
                    <div>
                        <h4 class="fw-bold mb-0">Find Projects</h4>
                        <p class="text-muted small mb-0">Discover businesses in an area, scan their websites, and qualify them for your services.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('campaigns.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Locations/Target Area <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control form-control-lg" required placeholder="e.g. Mumbai, Pune, Bengaluru (SMEs &amp; Growing Companies)" value="{{ old('location') }}">
                            <div class="form-text">The geographic area or market segment you want to prospect.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Campaign name</label>
                                <input type="text" name="name" class="form-control" placeholder="Optional — default is based on location" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Radius (km)</label>
                                <input type="number" step="any" min="0" name="radius_km" class="form-control" placeholder="Optional" value="{{ old('radius_km') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Minimum quality score</label>
                                <input type="number" min="0" max="100" name="min_score" class="form-control" value="{{ old('min_score', 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max businesses to target</label>
                                <input type="number" min="1" max="500" name="max_businesses" class="form-control" placeholder="Optional" value="{{ old('max_businesses') }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">What should be automated?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_analysis_enabled" id="auto" value="1" @checked((bool) old('auto_analysis_enabled', true))>
                                <label class="form-check-label" for="auto">Auto scan &amp; analyse websites — discover the right project, value &amp; opportunities</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="email_outreach_enabled" id="email" value="1" @checked((bool) old('email_outreach_enabled'))>
                                <label class="form-check-label" for="email">Auto-generate personalised outreach emails (you review before sending)</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2" id="startBtn">
                        <i class="bi bi-rocket-takeoff"></i> Start Discovery
                    </button>
                    <div class="text-center small text-muted mt-2"><i class="bi bi-shield-check me-1"></i>Compliant, human-reviewed sources only.</div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection