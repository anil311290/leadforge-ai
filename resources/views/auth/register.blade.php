<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account — {{ config('leadforge.product') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>*{font-family:'Inter',sans-serif}body{background:#f4f6fa}.auth-card{max-width:440px;margin:6vh auto;border-radius:.9rem;border:1px solid #e4e9f0}.brand{font-weight:800;font-size:1.3rem}.brand span{color:#2f86f6}</style>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>