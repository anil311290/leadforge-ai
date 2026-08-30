<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password — {{ config('leadforge.product') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>*{font-family:'Inter',sans-serif}body{background:#f4f6fa}.auth-card{max-width:420px;margin:8vh auto;border-radius:.9rem;border:1px solid #e4e9f0}.brand{font-weight:800;font-size:1.3rem}.brand span{color:#2f86f6}</style>
</head>
<body>
<div class="auth-card card shadow-sm p-4">
    <div class="text-center mb-3"><div class="brand">LeadForge <span>AI</span></div></div>
    <hr>
    <h5 class="fw-bold mb-1">Reset your password</h5>
    <p class="text-muted small">Enter your email and we'll send you a reset link.</p>
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
    @if(session('status'))<div class="alert alert-success py-2">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}" autofocus>
        </div>
        <button class="btn btn-primary w-100" type="submit">Send reset link</button>
    </form>
    <div class="text-center mt-3 small"><a href="{{ route('login') }}">Back to sign in</a></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>