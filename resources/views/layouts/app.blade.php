<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('leadforge.product')) — {{ config('leadforge.product') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
    <style>
        *{font-family:'Inter',sans-serif}
        body{background:#f4f6fa}
        .sidebar{width:250px;height:100vh;position:fixed;top:0;left:0;background:#0e1e3a;color:#eaf2ff;z-index:1040;transition:transform .2s;box-sizing:border-box;display:flex;flex-direction:column;overflow:hidden}
        .sidebar .brand{font-weight:800;font-size:1.15rem;color:#fff;padding:1.1rem 1.25rem .9rem;display:flex;align-items:center;gap:.5rem;letter-spacing:-.02em;flex-shrink:0}
        .sidebar .brand span{color:#5ea2ff}
        .sidebar-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:.25rem 0 .75rem;scrollbar-width:thin;scrollbar-color:rgba(181,198,222,.35) transparent}
        .sidebar-nav::-webkit-scrollbar{width:6px}
        .sidebar-nav::-webkit-scrollbar-thumb{background:rgba(181,198,222,.35);border-radius:99px}
        .sidebar-section{padding:.8rem 1.25rem .35rem;font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#b5c6de}
        .sidebar .nav-link{color:#edf4ff;margin:.08rem .75rem;padding:.64rem .75rem;display:flex;align-items:center;gap:.7rem;font-size:.96rem;border-radius:.7rem;border-left:0;opacity:1;transition:background .2s ease,color .2s ease,box-shadow .2s ease}
        .sidebar .nav-link i{font-size:1.1rem;opacity:.95;width:1.35rem;text-align:center;flex-shrink:0}
        .sidebar .nav-link:hover{color:#fff;background:rgba(255,255,255,.07)}
        .sidebar .nav-link.active{color:#fff;background:#17365f;font-weight:700;box-shadow:inset 3px 0 0 #5ea2ff,0 8px 24px rgba(0,0,0,.12)}
        .sidebar .text-muted{color:#b5c6de !important}
        .sidebar .small{letter-spacing:.12em;opacity:1}
        .sidebar-footer{flex-shrink:0;padding:.85rem 1rem 1rem;background:linear-gradient(180deg,rgba(14,30,58,0),#0e1e3a 24%);border-top:1px solid rgba(255,255,255,.08)}
        .sidebar-footer .btn{border-radius:.65rem;font-weight:700;padding:.55rem .75rem}
        .main{margin-left:250px;min-height:100vh;width:calc(100% - 250px);box-sizing:border-box}
        .topbar{background:#fff;border-bottom:1px solid #e4e9f0;position:sticky;top:0;z-index:1030}
        .main a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link){color:#286dcc;font-weight:600;text-decoration:none;text-underline-offset:3px;transition:color .15s ease}
        .main a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link):hover{color:#174f9f;text-decoration:underline}
        .main a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link):focus-visible{outline:3px solid rgba(47,134,246,.25);outline-offset:3px;border-radius:.2rem}
        .main a.text-muted:not(.btn){color:#718198 !important;font-weight:500}
        .main a.text-muted:not(.btn):hover{color:#286dcc !important}
        .main a.fw-semibold:not(.btn),.main a.fw-bold:not(.btn){font-weight:700}
        .stat-icon{width:42px;height:42px;border-radius:.6rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem}
        .score-pill{background:#0e1e3a;color:#fff;border-radius:99px;font-weight:700;font-size:.85rem;padding:.2rem .6rem}
        .badge-hot{background:#ff5470;color:#fff}
        .badge-high{background:#ff8a3d;color:#fff}
        .badge-medium{background:#ffd166;color:#333}
        .badge-low{background:#d7dee8;color:#333}
        .badge-ignore{background:#e9e9e9;color:#777}
        .empty-state{text-align:center;padding:2.5rem 1rem;color:#8a94a6}
        .empty-state i{font-size:2rem;color:#c2cad6}
        .text-accent{color:#2f86f6}
        .text-emerald{color:#10b981}
        .bg-emerald{background-color:#10b981}
        .dropdown-menu{font-size:.9rem}
        .pagination{margin-bottom:0;gap:.25rem;flex-wrap:wrap}
        .pagination .page-link{border-radius:.45rem;border-color:#d8e0ea;color:#27364a;font-size:.875rem;min-width:2rem;text-align:center}
        .pagination .page-item.active .page-link{background:#1e6fd9;border-color:#1e6fd9;color:#fff}
        .pagination .page-item.disabled .page-link{background:#f4f6fa;color:#8a94a6}
        .card-footer .pagination{justify-content:center}
        .campaign-card{transition:transform .15s,box-shadow .15s}
        .campaign-card:hover{transform:translateY(-2px);box-shadow:0 .5rem 1rem rgba(0,0,0,.08)!important}
        .bi-rocket-takeoff{font-size:18px !important}
        @media(max-width:991px){.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main{margin-left:0}}
    </style>
</head>
<body>
<nav class="sidebar" id="sidebar">
    <div class="brand"><i class="bi bi-lightning-charge-fill"></i> LeadForge <span>AI</span></div>
    <div class="sidebar-nav">
        <div class="sidebar-section">Workspace</div>
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}"><i class="bi bi-bullseye"></i> Find Projects</a>
        <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}"><i class="bi bi-people"></i> Leads</a>
        <a class="nav-link {{ request()->routeIs('pipeline.*') ? 'active' : '' }}" href="{{ route('pipeline.index') }}"><i class="bi bi-kanban"></i> Pipeline</a>
        <a class="nav-link {{ request()->routeIs('opportunities.*') ? 'active' : '' }}" href="{{ route('opportunities.index') }}"><i class="bi bi-lightning-charge"></i> Opportunities</a>
        <div class="sidebar-section">Outreach</div>
        <a class="nav-link {{ request()->routeIs('emails.*') ? 'active' : '' }}" href="{{ route('emails.index') }}"><i class="bi bi-envelope"></i> Emails</a>
        <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}" href="{{ route('followups.index') }}"><i class="bi bi-alarm"></i> Follow-ups</a>
        <div class="sidebar-section">Insights</div>
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-graph-up"></i> Reports</a>
        <a class="nav-link {{ request()->routeIs('ai.usage') ? 'active' : '' }}" href="{{ route('ai.usage') }}"><i class="bi bi-cpu"></i> AI Usage</a>
        <div class="sidebar-section">Freelancer.com</div>
        <a class="nav-link {{ request()->routeIs('freelancer.dashboard') ? 'active' : '' }}" href="{{ route('freelancer.dashboard') }}"><i class="bi bi-bar-chart-line"></i> Freelancer Overview</a>
        <a class="nav-link {{ request()->routeIs('freelancer.bids.*') ? 'active' : '' }}" href="{{ route('freelancer.bids.index') }}"><i class="bi bi-send"></i> Bid Tracking</a>
        <a class="nav-link {{ request()->routeIs('freelancer.accounts.*') ? 'active' : '' }}" href="{{ route('freelancer.accounts.index') }}"><i class="bi bi-person-badge"></i> Freelancer Accounts</a>
        <a class="nav-link {{ request()->routeIs('freelancer.settings.*') ? 'active' : '' }}" href="{{ route('freelancer.settings.index') }}"><i class="bi bi-sliders"></i> Freelancer Settings</a>
        @if(auth()->user() && auth()->user()->isAdmin())
        <div class="sidebar-section">Admin</div>
        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}"><i class="bi bi-boxes"></i> Services</a>
        <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><i class="bi bi-shield-lock"></i> Audit Trail</a>
        <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Settings</a>
        @endif
    </div>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> Sign out</button>
        </form>
    </div>
</nav>

<div class="main">
    <nav class="topbar d-flex justify-content-between align-items-center px-3 px-md-4 py-2">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light btn-sm d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="bi bi-list"></i></button>
            <span class="fw-semibold d-none d-md-inline">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-muted border">{{ ucfirst(auth()->user()->role ?? 'user') }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('profile.index') }}" class="btn btn-sm btn-light"><i class="bi bi-person me-1"></i>Profile</a>
        </div>
    </nav>
    <div class="p-3 p-md-4">
        @if(session('success'))<div class="alert alert-success py-2 d-none" id="flash-success" data-msg="{{ session('success') }}"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger py-2 d-none" id="flash-error" data-msg="{{ session('error') }}"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</div>@endif
        @yield('content')
    </div>
    <footer class="text-center text-muted small py-3">© {{ date('Y') }} {{ config('leadforge.owner') }} · {{ config('leadforge.tagline') }}</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<script>
// Toastr defaults
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4000,
    extendedTimeOut: 2000,
};

// Show flash messages as toastr
document.addEventListener('DOMContentLoaded', function () {
    const success = document.getElementById('flash-success');
    const error = document.getElementById('flash-error');
    if (success) toastr.success(success.dataset.msg);
    if (error) toastr.error(error.dataset.msg);
});

// Global SweetAlert2 delete confirmation
function confirmDelete(btn) {
    const form = btn.closest('form');
    const msg = form.dataset.confirm || 'Are you sure?';
    Swal.fire({
        title: 'Confirm',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@yield('scripts')
