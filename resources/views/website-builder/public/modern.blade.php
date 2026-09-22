@php
    $templateSlug = $templateSlug ?? 'modern-business';
    $primary = $brandPalette['primary'];
    $secondary = $brandPalette['secondary'];
    $accent = $brandPalette['accent'];
    $initials = collect(preg_split('/\s+/', trim($website->business_name)))->filter()->take(2)->map(fn ($word) => strtoupper(substr($word, 0, 1)))->join('');
    $about = $website->sections->firstWhere('section_type', 'about');
    $testimonials = $website->sections->where('section_type', 'testimonial')->where('is_enabled', true);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="{{ ($isPreview ?? false) ? 'noindex, nofollow' : 'index, follow' }}">
    <title>{{ $website->business_name }}</title>
    <meta name="description" content="{{ Str::limit($website->business_description ?: 'Learn more about '.$website->business_name.' and get in touch.', 155) }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        :root { --brand-primary: {{ $primary }}; --brand-secondary: {{ $secondary }}; --brand-accent: {{ $accent }}; }
        body { color: #172033; font-family: Inter, system-ui, sans-serif; background: #fbfcfe; }
        .site-nav { background: rgba(255,255,255,.9); border-bottom: 1px solid rgba(226,232,240,.8); backdrop-filter: blur(16px); }
        .site-nav .nav-brand { color: #172033; font-size: 1.05rem; letter-spacing: -.025em; }
        .site-nav .nav-link { color: #64748b; font-size: .84rem; font-weight: 650; }
        .site-nav .nav-link:hover { color: var(--brand-primary); }
        .brand-mark { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--brand-primary); color: #fff; font-weight: 800; letter-spacing: .04em; }
        .hero { background: linear-gradient(120deg, color-mix(in srgb, var(--brand-secondary) 96%, #fff), color-mix(in srgb, var(--brand-primary) 65%, var(--brand-secondary))); color: #fff; min-height: 620px; display: flex; align-items: center; }
        .hero .muted { color: rgba(255,255,255,.78); }
        .eyebrow { color: var(--brand-accent); font-size: .72rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .hero h1 { max-width: 760px; letter-spacing: -.055em; line-height: .98; }
        .hero-copy { max-width: 620px; }
        .hero-visual { min-height: 410px; position: relative; border-radius: 28px; overflow: hidden; background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.2); box-shadow: 0 24px 70px rgba(0,0,0,.22); }
        .hero-visual img { width: 100%; height: 100%; min-height: 410px; object-fit: cover; }
        .hero-visual-fallback { min-height: 410px; display: grid; place-items: center; background: linear-gradient(145deg, rgba(255,255,255,.2), rgba(255,255,255,.04)); }
        .hero-initials { width: 118px; height: 118px; display: grid; place-items: center; border: 1px solid rgba(255,255,255,.55); border-radius: 34px; color: #fff; font-size: 2.7rem; font-weight: 800; }
        .hero-caption { position: absolute; left: 20px; right: 20px; bottom: 20px; padding: 1rem 1.1rem; border-radius: 15px; background: rgba(10,20,35,.72); backdrop-filter: blur(12px); }
        .trust-strip { background: #fff; border-bottom: 1px solid #e8edf3; }
        .trust-item { color: #64748b; font-size: .78rem; font-weight: 650; }
        .trust-item i { color: var(--brand-primary); font-size: 1.05rem; }
        .section-pad { padding: 5.5rem 0; }
        .section-kicker { color: var(--brand-primary); font-size: .78rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        .section-intro { max-width: 650px; }
        .service-card, .product-card, .quote-card { border: 1px solid #e8edf3; border-radius: 18px; background: #fff; height: 100%; box-shadow: 0 12px 30px rgba(15,23,42,.04); transition: transform .2s ease, box-shadow .2s ease; }
        .service-card:hover, .product-card:hover { transform: translateY(-4px); box-shadow: 0 18px 36px rgba(15,23,42,.1); }
        .service-card { padding: 1.5rem; }
        .icon-box { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 12px; background: color-mix(in srgb, var(--brand-primary) 12%, #fff); color: var(--brand-primary); }
        .product-card { overflow: hidden; }
        .product-card img, .gallery-image { aspect-ratio: 4 / 3; object-fit: cover; width: 100%; }
        .contact-band { background: #f4f7fa; }
        .site-footer { background: var(--brand-secondary); color: rgba(255,255,255,.75); }
        .site-footer a { color: #fff; text-decoration: none; }
        .template-medical { background: #f4fbfa; }
        .template-medical .hero { background: linear-gradient(135deg, #064e3b, #0f766e); }
        .template-medical .service-card, .template-medical .product-card, .template-medical .quote-card { border-radius: 10px; }
        .template-medical .hero h1 { letter-spacing: -.04em; }
        .template-restaurant { background: #fffaf3; }
        .template-restaurant .hero { background: linear-gradient(135deg, #431407, #9a3412); }
        .template-restaurant .section-kicker, .template-restaurant .eyebrow { color: #f59e0b; }
        .template-restaurant .service-card, .template-restaurant .product-card, .template-restaurant .quote-card { border-radius: 4px; }
        .template-salon { background: #fff8fb; }
        .template-salon .hero { background: linear-gradient(135deg, #4a044e, #a21caf); }
        .template-salon .hero h1 { font-weight: 500 !important; letter-spacing: -.025em; }
        .template-salon .service-card, .template-salon .product-card, .template-salon .quote-card { border-radius: 28px; }
        .template-professional .hero { background: #111827; }
        .template-professional .section-pad { padding-top: 4.5rem; padding-bottom: 4.5rem; }
        .template-medical .hero { min-height: 580px; }
        .template-medical .hero-visual { border-radius: 18px; }
        .template-restaurant .hero h1 { font-family: Georgia, serif; letter-spacing: -.04em; }
        .template-restaurant .hero-visual { border-radius: 4px; }
        .template-salon .hero-visual { border-radius: 34px; }
        .template-professional .hero-visual { border-radius: 8px; }
        @media (max-width: 767px) { .section-pad { padding: 3.5rem 0; } .hero { min-height: auto; } .hero h1 { font-size: 2.7rem; } .hero-visual, .hero-visual img, .hero-visual-fallback { min-height: 300px; } }
    </style>
</head>
<body class="template-{{ $templateSlug }}">
    @if($isPreview ?? false)<div class="alert alert-warning rounded-0 mb-0 text-center small">Preview mode: this website is not public yet.</div>@endif
    @if(session('enquiry_success'))<div class="alert alert-success rounded-0 mb-0 text-center small">{{ session('enquiry_success') }}</div>@endif

    <nav class="site-nav sticky-top">
        <div class="container py-3 d-flex align-items-center gap-3">
            @if($website->logo)<img src="{{ asset('storage/'.$website->logo) }}" alt="{{ $website->business_name }} logo" style="width:44px;height:44px;object-fit:contain;">@else<div class="brand-mark">{{ $initials ?: 'B' }}</div>@endif
            <a href="#top" class="text-decoration-none nav-brand fw-bold">{{ $website->business_name }}</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a class="d-none d-md-inline text-decoration-none nav-link" href="#about">About</a>
                <a class="d-none d-md-inline text-decoration-none nav-link" href="#services">Services</a>
                <a class="d-none d-md-inline text-decoration-none nav-link" href="#products">Products</a>
                <a class="d-none d-md-inline text-decoration-none nav-link" href="#contact">Contact</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#productEnquiryModal">Enquire</button>
            </div>
        </div>
    </nav>

    <main id="top">
        <section class="hero section-pad">
            <div class="container"><div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-copy">
                    <div class="eyebrow mb-3">{{ match($templateSlug) { 'medical' => 'Healthcare and everyday wellness', 'restaurant' => 'Fresh flavours. Shared moments.', 'salon' => 'Style, care and confidence.', 'professional' => 'Trusted professional support.', default => 'Local expertise. Personal service.' } }}</div>
                    <h1 class="display-3 fw-bold mb-4">{{ $website->business_name }}</h1>
                    <p class="lead muted mb-4">{{ $website->business_description ?: 'Helping our community with dependable products and thoughtful service.' }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#productEnquiryModal">Ask about a product or service <i class="bi bi-arrow-right ms-1"></i></button>
                        @if($website->phone)<a href="tel:{{ $website->phone }}" class="btn btn-outline-light btn-lg">Call us</a>@endif
                    </div>
                    </div>
                </div>
                <div class="col-lg-5"><div class="hero-visual">
                    @if($website->media->firstWhere('type', 'banner'))
                        <img src="{{ asset('storage/'.$website->media->firstWhere('type', 'banner')->path) }}" alt="{{ $website->business_name }}">
                    @else
                        <div class="hero-visual-fallback"><div class="hero-initials">{{ $initials ?: 'B' }}</div></div>
                    @endif
                    <div class="hero-caption"><div class="eyebrow mb-1">{{ $website->city ?: 'Local business' }}</div><div class="fw-semibold">Here when you need us.</div></div>
                </div></div>
            </div></div>
        </section>

        <div class="trust-strip"><div class="container py-4"><div class="row g-3 text-center text-md-start">
            <div class="col-md-4 trust-item"><i class="bi bi-patch-check me-2"></i>Personal, local attention</div>
            <div class="col-md-4 trust-item"><i class="bi bi-chat-square-text me-2"></i>Enquiries answered directly</div>
            <div class="col-md-4 trust-item"><i class="bi bi-geo-alt me-2"></i>{{ trim($website->city.' '.$website->state) ?: 'Serving your community' }}</div>
        </div></div></div>

        <section id="about" class="section-pad"><div class="container"><div class="row g-5 align-items-center">
            <div class="col-lg-5"><div class="section-kicker mb-2">About us</div><h2 class="display-6 fw-bold">A better experience starts with being heard.</h2></div>
            <div class="col-lg-7"><p class="lead text-secondary mb-0">{{ $about?->content ?: ($website->business_description ?: 'We are committed to serving our local community with care, consistency and practical solutions.') }}</p></div>
        </div></div></section>

        @if($website->services->isNotEmpty())
            <section id="services" class="section-pad bg-light"><div class="container"><div class="mb-4"><div class="section-kicker mb-2">What we do</div><h2 class="display-6 fw-bold">Our services</h2></div><div class="row g-4">
                @foreach($website->services as $service)<div class="col-md-6 col-lg-4"><article class="service-card overflow-hidden">@if($service->image)<img src="{{ asset('storage/'.$service->image) }}" alt="{{ $service->service_name }}" style="width:100%;height:150px;object-fit:cover;">@endif<div class="p-4"><div class="icon-box mb-4"><i class="bi {{ $service->icon ?: 'bi-check2-circle' }} fs-5"></i></div><h3 class="h5 fw-bold">{{ $service->service_name }}</h3><p class="text-secondary">{{ $service->description ?: 'Talk to us to learn how we can help.' }}</p><button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productEnquiryModal" data-enquiry-subject="{{ $service->service_name }}" data-enquiry-kind="service">Enquire about this service</button></div></article></div>@endforeach
            </div></div></section>
        @endif

        @if($website->products->isNotEmpty())
            <section id="products" class="section-pad"><div class="container"><div class="mb-4"><div class="section-kicker mb-2">Explore our range</div><h2 class="display-6 fw-bold">Our products</h2><p class="text-secondary">Enquire for availability and details. Prices are shared based on your requirement.</p></div><div class="row g-4">
                @foreach($website->products as $product)<div class="col-sm-6 col-lg-4"><article class="product-card">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->product_name }}" style="width:100%;height:180px;object-fit:cover;">@endif<div class="p-4"><h3 class="h5 fw-bold">{{ $product->product_name }}</h3><p class="text-secondary small">{{ $product->description ?: 'Contact us for more information.' }}</p><button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productEnquiryModal" data-enquiry-subject="{{ $product->product_name }}" data-enquiry-kind="product">Enquire about this product</button></div></article></div>@endforeach
            </div></div></section>
        @endif

        @if($testimonials->isNotEmpty())<section class="section-pad bg-light"><div class="container"><div class="mb-4"><div class="section-kicker mb-2">Customer voices</div><h2 class="display-6 fw-bold">What people say</h2></div><div class="row g-4">@foreach($testimonials as $testimonial)<div class="col-md-6"><article class="quote-card p-4"><i class="bi bi-quote fs-2" style="color:var(--brand-accent)"></i><p class="lead mb-3">{{ $testimonial->content }}</p><div class="fw-semibold">{{ $testimonial->title ?: 'Customer' }}</div></article></div>@endforeach</div></div></section>@endif

        @if($website->media->isNotEmpty())<section class="section-pad"><div class="container"><div class="mb-4"><div class="section-kicker mb-2">Inside our world</div><h2 class="display-6 fw-bold">Gallery</h2></div><div class="row g-3">@foreach($website->media as $media)<div class="col-6 col-md-3"><img class="gallery-image rounded-3" src="{{ asset('storage/'.$media->path) }}" alt="{{ $media->label ?: $website->business_name }}" loading="lazy"></div>@endforeach</div></div></section>@endif

        <section id="contact" class="contact-band section-pad"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-7"><div class="section-kicker mb-2">Contact us</div><h2 class="display-6 fw-bold">Have a question? Let’s talk.</h2><p class="lead text-secondary">Reach out for a general question, location detail or business message.</p><button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#contactModal">Contact us</button></div><div class="col-lg-5"><div class="bg-white rounded-4 p-4 shadow-sm"><h3 class="h5 fw-bold mb-3">Find us</h3>@if($website->phone)<div class="mb-2"><i class="bi bi-telephone me-2" style="color:var(--brand-primary)"></i><a href="tel:{{ $website->phone }}" class="text-dark text-decoration-none">{{ $website->phone }}</a></div>@endif @if($website->email)<div class="mb-2"><i class="bi bi-envelope me-2" style="color:var(--brand-primary)"></i><a href="mailto:{{ $website->email }}" class="text-dark text-decoration-none">{{ $website->email }}</a></div>@endif @if($website->address || $website->city)<div><i class="bi bi-geo-alt me-2" style="color:var(--brand-primary)"></i>{{ $website->address ?: trim($website->city.' '.$website->state) }}</div>@endif</div></div></div></div></section>
    </main>

    <footer class="site-footer py-4"><div class="container d-flex flex-wrap justify-content-between gap-3"><div><strong class="text-white">{{ $website->business_name }}</strong><div class="small mt-1">Serving our community with care.</div></div><div class="small">&copy; {{ date('Y') }} {{ $website->business_name }}. All rights reserved.</div></div></footer>

    <div class="modal fade" id="productEnquiryModal" tabindex="-1" aria-labelledby="productEnquiryModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4"><div class="modal-header"><div><div class="section-kicker mb-1">Product or service enquiry</div><h2 class="modal-title h4 fw-bold" id="productEnquiryModalLabel">What would you like to know?</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form method="POST" action="{{ route('public.website.enquiry', $website->slug) }}"><div class="modal-body">@csrf<input type="hidden" name="enquiry_type" id="enquiryType" value="product"><div class="row g-3"><div class="col-12"><label class="form-label">Product or service *</label><input name="subject" id="enquirySubject" class="form-control" placeholder="What are you enquiring about?" required></div><div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required></div><div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div><div class="col-12"><label class="form-label">Phone</label><input name="phone" class="form-control"></div><div class="col-12"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="3" placeholder="Tell us what you need" required></textarea></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Send enquiry</button></div></form></div></div></div>
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4"><div class="modal-header"><div><div class="section-kicker mb-1">Contact us</div><h2 class="modal-title h4 fw-bold" id="contactModalLabel">Send a general message</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form method="POST" action="{{ route('public.website.enquiry', $website->slug) }}"><div class="modal-body">@csrf<input type="hidden" name="enquiry_type" value="contact"><div class="row g-3"><div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required></div><div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div><div class="col-12"><label class="form-label">Phone</label><input name="phone" class="form-control"></div><div class="col-12"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="4" placeholder="How can we help?" required></textarea></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Send message</button></div></form></div></div></div>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('[data-enquiry-subject]').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('enquirySubject').value = this.dataset.enquirySubject || '';
                document.getElementById('enquiryType').value = this.dataset.enquiryKind || 'product';
            });
        });
    </script>
</body>
</html>
