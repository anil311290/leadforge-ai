<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $website->business_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <style>:root { --brand-primary: {{ $website->primary_color ?: '#0d6efd' }}; --brand-secondary: {{ $website->secondary_color ?: '#212529' }}; --brand-accent: {{ $website->accent_color ?: '#ffc107' }}; } .brand-header { background: var(--brand-secondary); color: #fff; } .brand-button { background: var(--brand-primary); color: #fff; } .brand-accent { color: var(--brand-accent); }</style>
</head>
<body>
    <div class="brand-header py-4">
        <div class="container d-flex align-items-center gap-3">
            @if($website->logo)<img src="{{ asset('storage/'.$website->logo) }}" alt="{{ $website->business_name }} logo" style="max-width: 150px; max-height: 64px; object-fit: contain;">@endif
            <h1 class="fw-bold mb-0">{{ $website->business_name }}</h1>
        </div>
    </div>
    <div class="container py-5">
        <div class="text-center mb-5">
            @if($website->business_description)
                <p class="lead text-muted">{{ $website->business_description }}</p>
            @endif
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h2 class="h4 fw-bold">Contact</h2>
                    @if($website->phone)<p class="mb-1">Phone: {{ $website->phone }}</p>@endif
                    @if($website->email)<p class="mb-1">Email: {{ $website->email }}</p>@endif
                    @if($website->city || $website->state)<p class="mb-1">Location: {{ trim($website->city.' '.$website->state) }}</p>@endif
                    @if($website->address)<p class="mb-0">Address: {{ $website->address }}</p>@endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h2 class="h4 fw-bold">About</h2>
                    <p class="mb-0">{{ $website->business_description ?: 'Professional business website preview.' }}</p>
                </div>
            </div>
        </div>
        @if($website->media->isNotEmpty())
            <section class="mt-5">
                <h2 class="h4 fw-bold mb-3">Gallery</h2>
                <div class="row g-3">
                    @foreach($website->media as $media)
                        <div class="col-6 col-md-3"><img src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->label ?: $website->business_name }}" class="img-fluid rounded" loading="lazy"></div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</body>
</html>
