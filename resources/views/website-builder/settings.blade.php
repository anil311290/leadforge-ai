@extends('layouts.app')

@section('title', 'Website Builder Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Website Builder Settings</h4>
        <p class="text-muted small mb-0">Configure the public site base URL and defaults.</p>
    </div>
</div>

<div class="card shadow-sm border-0 p-4">
    <form method="POST" action="{{ route('website-builder.settings.update') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Public Website Base URL</label>
                <input type="url" name="website_base_url" class="form-control" value="{{ old('website_base_url', $settings['website_base_url'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Default Template</label>
                <input type="text" name="default_template" class="form-control" value="{{ old('default_template', $settings['default_template'] ?? 'modern-business') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Default AI Provider</label>
                <input type="text" name="default_ai_provider" class="form-control" value="{{ old('default_ai_provider', $settings['default_ai_provider'] ?? 'openai') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Default WhatsApp Message</label>
                <textarea name="default_whatsapp_message" class="form-control" rows="4">{{ old('default_whatsapp_message', $settings['default_whatsapp_message'] ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
