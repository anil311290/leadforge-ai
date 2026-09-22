@extends('layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Media Library</h4>
        <p class="text-muted small mb-0">Manage banner, gallery, product and service images for {{ $businessWebsite->business_name }}.</p>
    </div>
</div>

<div class="card shadow-sm border-0 p-4 mb-4">
    <h5 class="fw-bold mb-3">Website banner or gallery</h5>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <form method="POST" action="{{ route('website-builder.media.generate', $businessWebsite) }}">@csrf<input type="hidden" name="asset_type" value="banner"><button class="btn btn-primary" type="submit"><i class="bi bi-stars me-1"></i>Generate hero banner with AI</button></form>
        <span class="small text-muted align-self-center">AI creates the image automatically. Upload is optional.</span>
    </div>
    <form method="POST" action="{{ route('website-builder.media.store', $businessWebsite) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label">Image type</label>
            <select name="asset_type" class="form-select"><option value="banner">Hero banner</option><option value="gallery">Gallery image</option></select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Image</label>
            <input type="file" name="media" class="form-control" accept="image/*" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-control" maxlength="255">
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Upload</button></div>
    </form>
</div>

<div class="card shadow-sm border-0 p-4 mb-4">
    <h5 class="fw-bold mb-3">Product and service images</h5>
    <div class="row g-3">
        @foreach($businessWebsite->products as $product)
            <div class="col-md-6"><div class="border rounded p-3"><div class="fw-semibold mb-2">Product: {{ $product->product_name }}</div><div class="d-flex flex-wrap gap-2"><form method="POST" action="{{ route('website-builder.media.generate', $businessWebsite) }}">@csrf<input type="hidden" name="asset_type" value="product"><input type="hidden" name="item_id" value="{{ $product->id }}"><button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-stars me-1"></i>Generate with AI</button></form><form method="POST" action="{{ route('website-builder.media.item-image.store', $businessWebsite) }}" enctype="multipart/form-data"><input type="hidden" name="item_type" value="product"><input type="hidden" name="item_id" value="{{ $product->id }}">@csrf<input type="file" name="media" class="form-control form-control-sm d-inline-block" accept="image/*" required style="max-width:220px"><button class="btn btn-outline-primary btn-sm mt-2" type="submit">Upload</button></form></div>@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->product_name }}" class="mt-2 rounded" style="height:70px;width:100px;object-fit:cover;">@endif</div></div>
        @endforeach
        @foreach($businessWebsite->services as $service)
            <div class="col-md-6"><div class="border rounded p-3"><div class="fw-semibold mb-2">Service: {{ $service->service_name }}</div><div class="d-flex flex-wrap gap-2"><form method="POST" action="{{ route('website-builder.media.generate', $businessWebsite) }}">@csrf<input type="hidden" name="asset_type" value="service"><input type="hidden" name="item_id" value="{{ $service->id }}"><button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-stars me-1"></i>Generate with AI</button></form><form method="POST" action="{{ route('website-builder.media.item-image.store', $businessWebsite) }}" enctype="multipart/form-data"><input type="hidden" name="item_type" value="service"><input type="hidden" name="item_id" value="{{ $service->id }}">@csrf<input type="file" name="media" class="form-control form-control-sm d-inline-block" accept="image/*" required style="max-width:220px"><button class="btn btn-outline-primary btn-sm mt-2" type="submit">Upload</button></form></div>@if($service->image)<img src="{{ asset('storage/'.$service->image) }}" alt="{{ $service->service_name }}" class="mt-2 rounded" style="height:70px;width:100px;object-fit:cover;">@endif</div></div>
        @endforeach
    </div>
</div>

<div class="row g-3 mt-1">
    @forelse($businessWebsite->media as $media)
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <img src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->label ?: 'Gallery image' }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                <div class="card-body d-flex justify-content-between align-items-center gap-2">
                    <span class="small text-truncate">{{ ucfirst($media->type) }}: {{ $media->label ?: 'Untitled image' }}</span>
                    <form method="POST" action="{{ route('website-builder.media.destroy', [$businessWebsite, $media]) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border text-muted small">No gallery images uploaded yet.</div></div>
    @endforelse
</div>

<a href="{{ route('website-builder.websites.edit', $businessWebsite) }}" class="btn btn-outline-secondary mt-4">Back to Website</a>
@endsection
