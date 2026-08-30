@extends('layouts.app')
@section('title', $service->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('services.index') }}" class="small text-muted">&larr; Services</a>
        <h4 class="fw-bold mb-0">{{ $service->name }}</h4>
        <p class="text-muted small mb-0">{{ $service->category ?? '—' }} @if($service->min_value)· ₹{{ number_format($service->min_value) }} – ₹{{ number_format($service->max_value) }} @endif</p>
    </div>
    <span class="badge {{ $service->is_active?'text-bg-success':'bg-light text-dark border' }}">{{ $service->is_active?'Active':'Inactive' }}</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white fw-bold">Description</div>
    <div class="card-body small">{{ $service->description ?? 'No description provided.' }}</div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Detection Rules</div>
    <div class="card-body">
        <p class="small text-muted">Rules help the rule engine detect when a business likely needs this service.</p>
        <form method="POST" action="{{ route('services.rules.store', $service) }}" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-md-3"><label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="positive_signal">Positive signal</option>
                    <option value="negative_signal">Negative signal</option>
                    <option value="required_signal">Required signal</option>
                </select></div>
            <div class="col-md-3"><label class="form-label small">Keyword</label><input type="text" name="keyword" class="form-control form-control-sm" placeholder="e.g. wordpress"></div>
            <div class="col-md-2"><label class="form-label small">Weight</label><input type="number" name="weight" class="form-control form-control-sm" value="20" min="1" max="50"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Add rule</button></div>
        </form>

        @if($service->rules->count())
        <table class="table table-sm align-middle">
            <thead><tr><th>Type</th><th>Signal</th><th>Keyword</th><th>Weight</th><th></th></tr></thead>
            <tbody>
            @foreach($service->rules as $rule)
                <tr>
                    <td><span class="badge bg-light text-dark border">{{ $rule->type }}</span></td>
                    <td class="small">{{ $rule->signal ?? '—' }}</td>
                    <td class="small"><code>{{ $rule->keyword ?? '—' }}</code></td>
                    <td class="small">{{ $rule->weight }}</td>
                    <td>
                        <form method="POST" action="{{ route('services.rules.destroy', [$service, $rule]) }}" onsubmit="return confirm('Delete rule?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <div class="empty-state py-3"><i class="bi bi-sliders"></i><p class="mt-2">No rules yet. Add keywords like "wordpress", "manual", "excel" below.</p></div>
        @endif
    </div>
</div>
@endsection
