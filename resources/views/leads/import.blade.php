@extends('layouts.app')
@section('title', 'Import Leads')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Import Leads</h4>
                <p class="text-muted small">Paste business names/URLs or upload a CSV with <code>name, website, email, phone, industry, location</code> columns. We automatically de-duplicate against existing leads.</p>
                <hr>
                <form method="POST" action="{{ route('leads.import.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">CSV file</label>
                        <input type="file" name="csv" class="form-control" accept=".csv,.txt">
                    </div>
                    <div class="text-center text-muted small"><i class="bi bi-dash-lg"></i> or <i class="bi bi-dash-lg"></i></div>
                    <div class="my-3">
                        <label class="form-label fw-semibold">One business per line (name, website optional)</label>
                        <textarea name="businesses" class="form-control" rows="7" placeholder="Acme Traders&#10;example.com&#10;Bright Retail Pvt Ltd&#10;https://brightretail.in"></textarea>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-upload me-1"></i> Import</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection