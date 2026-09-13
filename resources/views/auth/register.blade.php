<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account — {{ config('leadforge.product') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body>
<div class="auth-card card shadow-sm p-4">
    <div class="text-center mb-3">
        <div class="brand">LeadForge <span>AI</span></div>
        <p class="text-muted small mb-0">Internal account creation</p>
    </div>
    <hr>
    <h5 class="fw-bold mb-1">Create your account</h5>
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Full name</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}" autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="8">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
    </form>
    <div class="text-center mt-3 small"><a href="{{ route('login') }}">Already have an account? Sign in</a></div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>