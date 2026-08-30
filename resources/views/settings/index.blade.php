@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="mb-3"><h4 class="fw-bold mb-1">Settings</h4><p class="text-muted small mb-0">Application configuration stored in the database.</p></div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Key/Values</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Company name</label><input type="text" name="company_name" class="form-control" value="{{ $settings->where('key','company_name')->first()->value ?? config('leadforge.owner') }}"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Email from name</label><input type="text" name="email_from_name" class="form-control" value="{{ $settings->where('key','email_from_name')->first()->value ?? config('leadforge.email.from_name') }}"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Default currency symbol</label><input type="text" name="currency_symbol" class="form-control" value="{{ $settings->where('key','currency_symbol')->first()->value ?? config('leadforge.currency_symbol') }}"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Email require approval</label>
                            <select name="email_require_approval" class="form-select">
                                <option value="1" @selected(($settings->where('key','email_require_approval')->first()->value ?? 1) == 1)>Yes — review before send</option>
                                <option value="0" @selected(($settings->where('key','email_require_approval')->first()->value ?? 1) == 0)>No — send immediately</option>
                            </select></div>
                    </div>
                    <button class="btn btn-primary">Save settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">All configuration keys</div>
            <div class="card-body py-2 small">
                @forelse($settings as $setting)
                    <div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted">{{ $setting->key }}</span><code>{{ $setting->value }}</code></div>
                @empty
                    <p class="text-muted py-2">No settings stored yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
