@extends('layouts.app')
@section('title', 'Freelancer Settings')

@section('content')
<div class="mb-3"><h4 class="fw-bold mb-1">Freelancer.com Settings</h4><p class="text-muted small mb-0">Global defaults for scanning and proposal generation. Per-account overrides live on each account's edit page.</p></div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('freelancer.settings.update') }}" id="freelancerSettingsForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Scan interval (minutes)</label><input type="number" min="1" max="1440" name="scan_interval_minutes" class="form-control" value="{{ $settings['scan_interval_minutes'] }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Min delay between bids (sec)</label><input type="number" min="0" max="600" name="delay_min_sec" class="form-control" value="{{ $settings['delay_min_sec'] }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Max delay between bids (sec)</label><input type="number" min="0" max="600" name="delay_max_sec" class="form-control" value="{{ $settings['delay_max_sec'] }}"></div>

                <div class="col-12"><label class="form-label fw-semibold">Default include keywords (comma separated)</label><input type="text" name="default_include_keywords" class="form-control" value="{{ implode(', ', (array) $settings['default_include_keywords']) }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Default exclude keywords (comma separated)</label><input type="text" name="default_exclude_keywords" class="form-control" value="{{ implode(', ', (array) $settings['default_exclude_keywords']) }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Default exclude client countries (ISO codes, comma separated)</label><input type="text" name="default_exclude_countries" class="form-control" value="{{ implode(', ', (array) $settings['default_exclude_countries']) }}"></div>

                <div class="col-12"><hr><div class="fw-semibold small text-muted text-uppercase mb-1">Default proposal profile (used only by accounts that don't set their own)</div></div>

                <div class="col-12">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="proposal_use_ai" value="1" {{ $settings['proposal_use_ai'] ? 'checked' : '' }}><label class="form-check-label fw-semibold">Generate proposals with AI (falls back to a template when off or unavailable)</label></div>
                </div>
                <div class="col-md-6"><label class="form-label fw-semibold">Profile title</label><input type="text" name="profile_title" class="form-control" value="{{ $settings['profile_title'] }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Portfolio URL</label><input type="url" name="portfolio_url" class="form-control" value="{{ $settings['portfolio_url'] }}"><div class="form-text">Fallback only — set a different one per account on its edit page. Mentioned in a proposal only when the project asks for a portfolio/past work link.</div></div>
                <div class="col-12"><label class="form-label fw-semibold">Profile summary</label><textarea name="profile_summary" class="form-control" rows="3">{{ $settings['profile_summary'] }}</textarea></div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary" id="saveFreelancerSettingsBtn">Save settings</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('freelancerSettingsForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('saveFreelancerSettingsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

    const formData = new FormData(this);
    const token = this.querySelector('input[name="_token"]').value;

    fetch(this.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: formData,
    })
    .then(async res => {
        const data = await res.json().catch(() => null);
        if (data && data.success) toastr.success(data.success);
        else toastr.success('Settings saved.');
    })
    .catch(err => toastr.error('Error: ' + err.message))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Save settings';
    });
});
</script>
@endsection
