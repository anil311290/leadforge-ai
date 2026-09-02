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

                    <!-- Discovery Sources -->
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1 mb-3">
                            <i class="bi bi-search text-primary"></i> Discovery Sources <span class="text-danger">*</span>
                        </label>
                        <p class="small text-muted mb-3">Choose where to find leads. AI Web Search automatically finds businesses matching your target area.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="ai_web_search" id="src-ai" checked>
                                    <label class="form-check-label fw-semibold" for="src-ai">
                                        <i class="bi bi-robot text-primary me-1"></i> AI Web Search
                                    </label>
                                    <div class="small text-muted mt-1">Automatically finds businesses using AI — just tell us location &amp; industry. Like Google + LinkedIn search combined.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="manual_urls" id="src-manual" checked>
                                    <label class="form-check-label fw-semibold" for="src-manual">
                                        <i class="bi bi-pencil-square text-success me-1"></i> Manual Entry
                                    </label>
                                    <div class="small text-muted mt-1">Use business names/URLs you enter below. Good when you already have a list.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="csv" id="src-csv">
                                    <label class="form-check-label fw-semibold" for="src-csv">
                                        <i class="bi bi-file-earmark-spreadsheet text-warning me-1"></i> CSV Import
                                    </label>
                                    <div class="small text-muted mt-1">Upload a CSV file with business data. Use the Import page first.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="search_api" id="src-api">
                                    <label class="form-check-label fw-semibold" for="src-api">
                                        <i class="bi bi-globe text-info me-1"></i> Google/LinkedIn API
                                    </label>
                                    <div class="small text-muted mt-1">Connect via API key for real-time Google My Business or LinkedIn search results.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Target Businesses (shown when manual is selected) -->
                    <div class="mb-4" id="manualBusinessesSection">
                        <label class="form-label fw-semibold d-flex align-items-center gap-1">
                            <i class="bi bi-building text-secondary"></i> Target Businesses <span class="text-muted fw-normal small">(optional — only if Manual Entry is selected above)</span>
                        </label>

                        <!-- Sample business chips -->
                        <div class="d-flex flex-wrap gap-1 mb-2" id="sampleChips">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('Acme Traders Pvt Ltd')">Acme Traders</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('BrightTech Solutions')">BrightTech Solutions</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('Global Retail Mart')">Global Retail Mart</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('Nova Digital Services')">Nova Digital</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('Quick Solutions Inc')">Quick Solutions</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('https://example.com')">example.com</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBusiness('https://demo-store.in')">demo-store.in</button>
                        </div>

                        <textarea name="businesses" class="form-control" rows="3" id="businessesTextarea"
                                  placeholder="e.g.&#10;Acme Traders Pvt Ltd&#10;https://brightretail.in&#10;Tech Solutions&#10;https://techsolutions.com">{{ old('businesses') }}</textarea>
                        <div class="form-text mt-1 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <span>Enter business names or website URLs — one per line. Click any example above to add it.</span>
                        </div>
                    </div>

                    <script>
                    function addBusiness(name) {
                        const ta = document.getElementById('businessesTextarea');
                        const current = ta.value.trim();
                        ta.value = current ? current + '\n' + name : name;
                        ta.focus();
                    }
                    // Toggle manual businesses section based on checkbox
                    document.addEventListener('DOMContentLoaded', function() {
                        const manualCheckbox = document.getElementById('src-manual');
                        const section = document.getElementById('manualBusinessesSection');
                        function toggleManual() {
                            section.style.display = manualCheckbox.checked ? 'block' : 'none';
                        }
                        manualCheckbox.addEventListener('change', toggleManual);
                        toggleManual();
                    });
                    </script>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2 py-3" id="startBtn">
                        <i class="bi bi-rocket-takeoff fs-5" id="startIcon"></i>
                        <span class="fw-semibold" id="startText">Start Discovery</span>
                        <span class="spinner-border spinner-border-sm d-none" id="startSpinner"></span>
                    </button>
                    <div class="text-center small text-muted mt-3">
                        <i class="bi bi-shield-check me-1 text-success"></i>Compliant, human-reviewed sources only.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('startBtn')?.addEventListener('click', function(e) {
    const btn = this;
    const icon = document.getElementById('startIcon');
    const text = document.getElementById('startText');
    const spinner = document.getElementById('startSpinner');
    btn.disabled = true;
    icon.classList.add('d-none');
    text.textContent = 'Starting Discovery…';
    spinner.classList.remove('d-none');
});
</script>
@endsection