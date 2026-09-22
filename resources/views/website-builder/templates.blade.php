@extends('layouts.app')

@section('title', 'Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Templates</h4>
        <p class="text-muted small mb-0">Choose a reusable design for your business website.</p>
    </div>
</div>

<div class="row g-3">
    @foreach($templates as $template)
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold mb-0">{{ $template['name'] }}</h6>
                        <span class="badge bg-light text-dark border">{{ $template['slug'] }}</span>
                    </div>
                    <p class="text-muted small mb-3">Reusable sections and modern layout for professional service businesses.</p>
                    <button class="btn btn-primary btn-sm">Select Template</button>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
