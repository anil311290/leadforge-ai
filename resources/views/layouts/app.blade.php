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
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
    <script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}" defer></script>
</head>
<body>
<nav class="sidebar" id="sidebar">
    <div class="brand"><i class="bi bi-lightning-charge-fill"></i> LeadForge <span>AI</span></div>
    <div class="sidebar-nav">
        @if(auth()->user() && auth()->user()->isWebsiteBuilder())
        <div class="sidebar-section">Website Builder</div>
        <a class="nav-link {{ request()->routeIs('website-builder.dashboard') ? 'active' : '' }}" href="{{ route('website-builder.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('website-builder.websites.*') ? 'active' : '' }}" href="{{ route('website-builder.websites.index') }}"><i class="bi bi-window-stack"></i> Websites</a>
        <a class="nav-link {{ request()->routeIs('website-builder.templates.*') ? 'active' : '' }}" href="{{ route('website-builder.templates.index') }}"><i class="bi bi-layout-text-window-reverse"></i> Templates</a>
        <a class="nav-link {{ request()->routeIs('website-builder.settings.*') ? 'active' : '' }}" href="{{ route('website-builder.settings.index') }}"><i class="bi bi-gear"></i> Settings</a>
        @else
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
        @endif
    </div>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> Sign out</button>
        </form>
    </div>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('show'); this.classList.remove('show');"></div>

<div class="main">
    <nav class="topbar d-flex justify-content-between align-items-center px-3 px-md-4 py-2">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light btn-sm d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show'); document.getElementById('sidebarOverlay').classList.toggle('show');"><i class="bi bi-list"></i></button>
            <span class="fw-semibold d-none d-md-inline">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-muted border">{{ ucfirst(auth()->user()->role ?? 'user') }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(! auth()->user()->isWebsiteBuilder())
                <a href="{{ route('profile.index') }}" class="btn btn-sm btn-light"><i class="bi bi-person me-md-1"></i><span class="topbar-label">Profile</span></a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Sign out"><i class="bi bi-box-arrow-right me-md-1"></i><span class="topbar-label">Sign out</span></button>
            </form>
        </div>
    </nav>
    <div class="p-3 p-md-4">
        @if(session('success'))<div class="alert alert-success py-2 d-none" id="flash-success" data-msg="{{ session('success') }}"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger py-2 d-none" id="flash-error" data-msg="{{ session('error') }}"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</div>@endif
        @yield('content')
    </div>
    <footer class="text-center text-muted small py-3">© {{ date('Y') }} {{ config('leadforge.owner') }} · {{ config('leadforge.tagline') }}</footer>
</div>

<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}"></script>
<script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/vendor/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
@yield('scripts')
