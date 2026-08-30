<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('leadforge.product') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family:'Inter', sans-serif; }
        body { background:#f4f6fa; }
        .auth-card { max-width:420px; margin:8vh auto; border-radius:.9rem; border:1px solid #e4e9f0; }
        .brand { font-weight:800; font-size:1.3rem; }
        .brand span { color:#2f86f6; }
    </style>
</head>
<body>
<div class="auth-card card shadow-sm p-4">
    <div class="text-center mb-3">
        <div class="brand">LeadForge <span>AI</span></div>
        <p class="text-muted small mb-0">{{ config('leadforge.tagline') }}</p>
    </div>
    <hr>
    <h5 class="fw-bold mb-1">Welcome back</h5>
    <p class="text-muted small">Sign in to your internal account.</p>

    @if($errors->any())
        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-info py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus value="{{ old('email') }}" autocomplete="email">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="current-password">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('password.request') }}" class="small text-muted">Forgot password?</a>
    </div>
    <div class="text-center mt-2 small text-muted">
        {{ config('leadforge.owner') }}
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>