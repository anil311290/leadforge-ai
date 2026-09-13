<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LeadForge AI automates client discovery, deep website signal analysis, tailored proposal generation, and Freelancer.com auto-bidding.">
    <title>{{ config('leadforge.product') }} — Sales Intelligence & Project Discovery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}?v={{ filemtime(public_path('assets/css/landing.css')) }}">
</head>
<body>
    <!-- Ambient Background Lighting -->
    <div class="bg-ambient" aria-hidden="true">
        <div class="ambient-grid"></div>
        <div class="ambient-blob-1"></div>
        <div class="ambient-blob-2"></div>
    </div>

    <!-- Floating Glass Navbar -->
    <div class="navbar-wrapper">
        <header class="navbar">
            <a class="brand-logo" href="{{ url('/') }}">
                <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                <span>LeadForge <span class="brand-badge">AI</span></span>
            </a>

            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#workflow">How It Works</a></li>
                <li><a href="#freelancer">Auto-Bidding</a></li>
                <li><a href="#comparison">Why LeadForge</a></li>
                <li><a href="#faq">FAQ</a></li>
            </ul>

            <div class="nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm btn-glow">
                        <span>Sign in</span> <i class="bi bi-arrow-right-short"></i>
                    </a>
                @endauth
                <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </header>
    </div>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNav">
        <ul class="mobile-nav-links">
            <li><a href="#features" onclick="toggleMobileNav()">Features</a></li>
            <li><a href="#workflow" onclick="toggleMobileNav()">How It Works</a></li>
            <li><a href="#freelancer" onclick="toggleMobileNav()">Auto-Bidding</a></li>
            <li><a href="#comparison" onclick="toggleMobileNav()">Why LeadForge</a></li>
            <li><a href="#faq" onclick="toggleMobileNav()">FAQ</a></li>
        </ul>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary w-100"><i class="bi bi-speedometer2"></i> Open Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary w-100">Sign in to Account</a>
            @endauth
        </div>
    </div>

    <main>
        <!-- HERO SECTION -->
        <section class="hero-section">
            <div class="container">
                <div class="hero-pill">
                    <span class="pulse-dot"></span>
                    <span>Sales Intelligence & Project Discovery 2.0</span>
                </div>

                <h1 class="hero-title">
                    Turn Public Business Signals Into <br>
                    <span class="gradient-text">High-Value Client Deals</span>
                </h1>

                <p class="hero-desc">
                    LeadForge AI automates client discovery, technical website auditing, custom proposal writing, and Freelancer.com autopilot bidding in one unified revenue engine.
                </p>

                <div class="hero-cta-group">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg btn-glow">
                        <i class="bi bi-rocket-takeoff"></i>
                        <span>Sign In to Dashboard</span>
                    </a>
                    <a href="#workflow" class="btn btn-secondary btn-lg">
                        <i class="bi bi-play-circle"></i>
                        <span>See How It Works</span>
                    </a>
                </div>

                <div class="hero-trust">
                    <span><i class="bi bi-check-circle-fill"></i> No credit card required</span>
                    <span><i class="bi bi-check-circle-fill"></i> Real-time signal crawler</span>
                    <span><i class="bi bi-check-circle-fill"></i> Multi-account Freelancer support</span>
                </div>

                <!-- Interactive App Dashboard Mockup -->
                <div class="mockup-wrapper">
                    <div class="mockup-inner">
                        <div class="mockup-header">
                            <div class="window-dots">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="mockup-search-bar">
                                <i class="bi bi-search"></i>
                                <span>leadforge.app/campaigns/real-time-discovery</span>
                            </div>
                            <div class="mockup-status-live">
                                <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                                <span>LIVE ENGINE</span>
                            </div>
                        </div>
                        <div class="mockup-body">
                            <div class="mockup-sidebar">
                                <div class="mockup-nav-item active"><i class="bi bi-speedometer2"></i> Overview</div>
                                <div class="mockup-nav-item"><i class="bi bi-bullseye"></i> Find Projects</div>
                                <div class="mockup-nav-item"><i class="bi bi-people"></i> Qualified Leads</div>
                                <div class="mockup-nav-item"><i class="bi bi-kanban"></i> Deal Pipeline</div>
                                <div class="mockup-nav-item"><i class="bi bi-send"></i> Freelancer Bids</div>
                                <div class="mockup-nav-item"><i class="bi bi-envelope"></i> Smart Outreach</div>
                            </div>
                            <div class="mockup-content">
                                <div class="mockup-stat-row">
                                    <div class="mockup-stat-card">
                                        <div class="label">Discovered Leads</div>
                                        <div class="val">482</div>
                                        <div class="trend"><i class="bi bi-arrow-up-right"></i> +34 today</div>
                                    </div>
                                    <div class="mockup-stat-card">
                                        <div class="label">Avg AI Score</div>
                                        <div class="val">88%</div>
                                        <div class="trend"><i class="bi bi-lightning-charge"></i> High Intent</div>
                                    </div>
                                    <div class="mockup-stat-card">
                                        <div class="label">Pipeline Value</div>
                                        <div class="val">₹38.5L</div>
                                        <div class="trend"><i class="bi bi-graph-up"></i> 14 Opportunities</div>
                                    </div>
                                    <div class="mockup-stat-card">
                                        <div class="label">Active Bids</div>
                                        <div class="val">26</div>
                                        <div class="trend" style="color:#38bdf8;"><i class="bi bi-check2"></i> 80% Floor Protected</div>
                                    </div>
                                </div>

                                <div class="mockup-table-card">
                                    <div class="mockup-table-header">
                                        <span>Highest Potential Opportunities</span>
                                        <span style="color: var(--accent-teal); font-size: 0.75rem; cursor: pointer;">Auto-Scored by AI <i class="bi bi-stars"></i></span>
                                    </div>
                                    <div class="mockup-row" style="background: rgba(255,255,255,0.02); font-weight: 700; color: var(--text-dim); text-transform: uppercase; font-size: 0.68rem;">
                                        <span>Target Business</span>
                                        <span class="hide-mobile">Detected Gap</span>
                                        <span>AI Fit Score</span>
                                        <span>Est. Deal</span>
                                        <span>Action</span>
                                    </div>
                                    <div class="mockup-row">
                                        <div>
                                            <div class="company-name">Apex Global Logistics</div>
                                            <div class="company-loc">Transport & Fleet · Mumbai</div>
                                        </div>
                                        <div class="hide-mobile" style="color: #cbd5e1;">Custom Tracking Portal</div>
                                        <div><span class="badge-score score-hot">94 / 100</span></div>
                                        <div style="font-weight: 700; color: #38bdf8;">₹3,50,000</div>
                                        <div><span class="btn btn-primary btn-sm" style="padding: 3px 10px; font-size: 0.7rem;">Draft Outreach</span></div>
                                    </div>
                                    <div class="mockup-row">
                                        <div>
                                            <div class="company-name">Horizon Dental Care</div>
                                            <div class="company-loc">Healthcare · Bengaluru</div>
                                        </div>
                                        <div class="hide-mobile" style="color: #cbd5e1;">Website Redesign + Booking</div>
                                        <div><span class="badge-score score-high">89 / 100</span></div>
                                        <div style="font-weight: 700; color: #38bdf8;">₹1,20,000</div>
                                        <div><span class="btn btn-primary btn-sm" style="padding: 3px 10px; font-size: 0.7rem;">Draft Outreach</span></div>
                                    </div>
                                    <div class="mockup-row">
                                        <div>
                                            <div class="company-name">Aura Luxe Fashion</div>
                                            <div class="company-loc">Retail · New Delhi</div>
                                        </div>
                                        <div class="hide-mobile" style="color: #cbd5e1;">E-Commerce & CRM Sync</div>
                                        <div><span class="badge-score score-mid">84 / 100</span></div>
                                        <div style="font-weight: 700; color: #38bdf8;">₹2,40,000</div>
                                        <div><span class="btn btn-primary btn-sm" style="padding: 3px 10px; font-size: 0.7rem;">Draft Outreach</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATS TICKER STRIP -->
        <div class="stats-strip">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-num">10x</div>
                        <div class="stat-label">Faster Prospect Research</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">80%+</div>
                        <div class="stat-label">Budget Floor Margin Safeguard</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">0%</div>
                        <div class="stat-label">Duplicate AI Proposals</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">24/7</div>
                        <div class="stat-label">Signal & Lead Discovery</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BENTO FEATURES GRID -->
        <section class="section" id="features">
            <div class="container">
                <div class="section-header">
                    <div class="section-kicker">
                        <i class="bi bi-grid-fill"></i> Full Revenue Suite
                    </div>
                    <h2 class="section-title">Everything you need to discover, qualify & win.</h2>
                    <p class="section-desc">
                        No more cold emailing random lists or competing in generic bidding races. LeadForge AI connects every step with actual verified signals.
                    </p>
                </div>

                <div class="bento-grid">
                    <!-- Card 1: Discovery -->
                    <div class="bento-card bento-span-2">
                        <span class="bento-pill-tag">Intelligence Engine</span>
                        <div class="bento-icon"><i class="bi bi-geo-alt"></i></div>
                        <h3 class="bento-card-title">Multi-Channel Discovery Campaigns</h3>
                        <p class="bento-card-desc">
                            Target local businesses by city, radius, and industry across Google Places, custom search queries, CSV list uploads, and raw URLs. LeadForge automatically verifies contact emails, phone numbers, and WhatsApp channels.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> Automatic phone & WhatsApp normalization</li>
                            <li><i class="bi bi-check2-circle"></i> Deduplication across all historical discovery campaigns</li>
                            <li><i class="bi bi-check2-circle"></i> Batch execution with pause, resume & regenerate controls</li>
                        </ul>
                    </div>

                    <!-- Card 2: AI Lead Analysis -->
                    <div class="bento-card">
                        <span class="bento-pill-tag">Deep Auditing</span>
                        <div class="bento-icon"><i class="bi bi-cpu"></i></div>
                        <h3 class="bento-card-title">Deep AI Site Analysis</h3>
                        <p class="bento-card-desc">
                            Our crawler inspects technologies, mobile usability, performance bottlenecks, and missing capabilities to build a compelling technical sales pitch.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> Missing capabilities detection</li>
                            <li><i class="bi bi-check2-circle"></i> Technology gap scoring</li>
                        </ul>
                    </div>

                    <!-- Card 3: Opportunity Scoring -->
                    <div class="bento-card">
                        <span class="bento-pill-tag">8-Point Matrix</span>
                        <div class="bento-icon"><i class="bi bi-stars"></i></div>
                        <h3 class="bento-card-title">0–100 Opportunity Ranking</h3>
                        <p class="bento-card-desc">
                            Every lead is mathematically evaluated on business fit, urgency, company potential, and budget scale so you only invest time on high-converting prospects.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> HOT, HIGH, MEDIUM & LOW classifications</li>
                            <li><i class="bi bi-check2-circle"></i> Automated budget range projections</li>
                        </ul>
                    </div>

                    <!-- Card 4: Freelancer Auto-Bidding -->
                    <div class="bento-card bento-span-2" id="freelancer">
                        <span class="bento-pill-tag">Autopilot Marketplace</span>
                        <div class="bento-icon"><i class="bi bi-send-check"></i></div>
                        <h3 class="bento-card-title">Freelancer.com Smart Auto-Bidding</h3>
                        <p class="bento-card-desc">
                            Connect multiple Freelancer.com accounts with individual OAuth tokens, experience profiles, and custom writing styles. Automatically places bids with our smart 80% budget floor, 5-unit rounding, and private internal estimates for negotiation.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> 100% unique proposals per account (no repetitive templates)</li>
                            <li><i class="bi bi-check2-circle"></i> Account-wise performance dashboard with daily bid capacity limits</li>
                            <li><i class="bi bi-check2-circle"></i> Private internal costing & realistic timeline tracker</li>
                        </ul>
                    </div>

                    <!-- Card 5: Outreach & Quotation -->
                    <div class="bento-card">
                        <span class="bento-pill-tag">Omnichannel Outreach</span>
                        <div class="bento-icon"><i class="bi bi-envelope-paper"></i></div>
                        <h3 class="bento-card-title">Smart Email & WhatsApp</h3>
                        <p class="bento-card-desc">
                            Generate context-aware cold emails referencing the exact problems found on the lead's site. Schedule automated follow-ups that stop when the client replies.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> 1-click WhatsApp web messaging</li>
                            <li><i class="bi bi-check2-circle"></i> Automated commercial quotation generator</li>
                        </ul>
                    </div>

                    <!-- Card 6: Pipeline Kanban -->
                    <div class="bento-card bento-span-2">
                        <span class="bento-pill-tag">Deal Management</span>
                        <div class="bento-icon"><i class="bi bi-kanban"></i></div>
                        <h3 class="bento-card-title">Visual Sales Pipeline & Activity Log</h3>
                        <p class="bento-card-desc">
                            Drag-and-drop deal board tracking stages from Discovered to Proposal, Negotiation, and Won. Complete audit logs and team activity tracking for full transparency.
                        </p>
                        <ul class="bento-feature-list">
                            <li><i class="bi bi-check2-circle"></i> Real-time pipeline value & win rate calculations</li>
                            <li><i class="bi bi-check2-circle"></i> Lead claiming, internal notes & audit trails</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- WORKFLOW JOURNEY -->
        <section class="section" id="workflow" style="background: rgba(13, 22, 39, 0.3); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
            <div class="container">
                <div class="section-header">
                    <div class="section-kicker">
                        <i class="bi bi-diagram-3-fill"></i> Streamlined Workflow
                    </div>
                    <h2 class="section-title">From first search to signed project in 4 steps.</h2>
                    <p class="section-desc">
                        Turn prospecting from chaotic manual outreach into an organized, repeatable software pipeline.
                    </p>
                </div>

                <div class="workflow-grid">
                    <div class="workflow-card">
                        <div class="step-num">STEP 01</div>
                        <h3>Launch Campaign</h3>
                        <p>Define target location, keywords, or import business lists. LeadForge scrapes public signals and gathers domain profiles.</p>
                    </div>

                    <div class="workflow-card">
                        <div class="step-num">STEP 02</div>
                        <h3>AI Signal Audit</h3>
                        <p>Our analysis engine checks digital gaps, missing features, and calculates realistic service quotes with opportunity scores.</p>
                    </div>

                    <div class="workflow-card">
                        <div class="step-num">STEP 03</div>
                        <h3>Generate Proposals</h3>
                        <p>AI crafts distinct, tailored proposals incorporating account experience level, selected writing tone, and matched portfolio items.</p>
                    </div>

                    <div class="workflow-card">
                        <div class="step-num">STEP 04</div>
                        <h3>Engage & Close</h3>
                        <p>Send approved cold emails, trigger follow-up sequences, connect via WhatsApp, and track deal stages in the visual pipeline.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- COMPARISON SECTION -->
        <section class="section" id="comparison">
            <div class="container">
                <div class="section-header">
                    <div class="section-kicker">
                        <i class="bi bi-award-fill"></i> Competitive Edge
                    </div>
                    <h2 class="section-title">Manual Prospecting vs LeadForge AI</h2>
                    <p class="section-desc">
                        Compare traditional freelance hunting against an automated signal-driven pipeline.
                    </p>
                </div>

                <div class="comparison-wrapper">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Feature / Workflow</th>
                                <th class="highlight"><i class="bi bi-lightning-charge-fill"></i> LeadForge AI</th>
                                <th>Manual Hunting</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="feature-name">Client Discovery Speed</td>
                                <td class="leadforge-val">Hundreds of leads in 60 seconds</td>
                                <td style="color: var(--text-dim);">10–15 businesses per hour</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Website Technical Audit</td>
                                <td class="leadforge-val">Automated deep AI crawl & signal scoring</td>
                                <td style="color: var(--text-dim);">Manual inspection & guesswork</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Proposal Uniqueness</td>
                                <td class="leadforge-val">100% unique per account tone & experience</td>
                                <td style="color: var(--text-dim);">Generic copy-paste templates</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Freelancer.com Bidding</td>
                                <td class="leadforge-val">Autopilot multi-account with 80% budget floor</td>
                                <td style="color: var(--text-dim);">Manual bidding, racing to the bottom</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Cost & Timeline Calculations</td>
                                <td class="leadforge-val">Private internal cost & timeline guidance</td>
                                <td style="color: var(--text-dim);">Unstructured estimations</td>
                            </tr>
                            <tr>
                                <td class="feature-name">Follow-up Sequences</td>
                                <td class="leadforge-val">Automated drip stopping on reply</td>
                                <td style="color: var(--text-dim);">Easily forgotten in inbox</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- FAQ SECTION -->
        <section class="section" id="faq" style="background: rgba(13, 22, 39, 0.2); border-top: 1px solid var(--border-subtle);">
            <div class="container">
                <div class="section-header">
                    <div class="section-kicker">
                        <i class="bi bi-question-circle-fill"></i> Common Questions
                    </div>
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-desc">Everything you need to know about LeadForge AI and our discovery engine.</p>
                </div>

                <div class="faq-grid">
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How does Freelancer.com auto-bidding work?</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            You connect your Freelancer.com developer OAuth key with your budget and keyword rules. LeadForge scans newly posted projects, filters out low-budget or irrelevant posts, and uses AI to generate custom tailored proposals. With Auto-Submit ON, bids are placed automatically within your daily limit. With Auto-Submit OFF, bids are saved for 1-click manual review.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Are generated proposals unique for different accounts?</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            Yes! Every account configured in LeadForge has its own name, profile title, experience years, and proposal style (Technical, Direct, Consultative, Conversational, or Agile). The AI strictly crafts unique phrasing, hooks, and perspectives so multiple accounts never submit identical bids.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What discovery sources are supported?</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            LeadForge supports Google Places search API, AI web search, manual URL entries, and bulk CSV uploads. You can target specific cities, industries, and business types to build an instant pipeline.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How does the 80% budget floor rule protect pricing?</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            Instead of placing bids at the minimum rate, LeadForge calculates bids strictly above 80% of the client's published maximum budget ceiling and rounds the amount up to a clean multiple of 5 in the project's currency. It also calculates a separate private internal cost to guide your final client negotiations.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- HIGH CONVERTING CALL TO ACTION -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-box">
                    <h2 class="cta-title">Ready to Win High-Paying Projects?</h2>
                    <p class="cta-subtitle">
                        Stop hunting manually. Join modern agencies and freelancers building automated sales pipelines with LeadForge AI.
                    </p>
                    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg btn-glow">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Sign in to Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- MODERN FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a class="brand-logo" href="{{ url('/') }}">
                        <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                        <span>LeadForge <span class="brand-badge">AI</span></span>
                    </a>
                    <p>
                        Sales intelligence, automated client discovery, and Freelancer.com auto-bidding platform for digital builders.
                    </p>
                </div>

                <div class="footer-col">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#workflow">Workflow</a></li>
                        <li><a href="#freelancer">Auto-Bidding</a></li>
                        <li><a href="#comparison">Comparison</a></li>
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Modules</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Discovery Campaigns</a></li>
                        <li><a href="{{ route('login') }}">AI Website Scan</a></li>
                        <li><a href="{{ route('login') }}">Opportunity Matrix</a></li>
                        <li><a href="{{ route('login') }}">Deal Pipeline</a></li>
                        <li><a href="{{ route('login') }}">Email Automation</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Compliance & Security</h4>
                    <p style="font-size: 0.8rem; line-height: 1.6; color: var(--text-dim);">
                        Compliant public data discovery only. OAuth tokens stored with AES-256 encryption at rest. Human-in-the-loop review safeguards.
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>© {{ date('Y') }} {{ config('leadforge.owner') }} · {{ config('leadforge.product') }}. All rights reserved.</div>
                <div>Find the Right Business · Discover the Right Project</div>
            </div>
        </div>
    </footer>

    <!-- Interactive Vanilla JS -->
    <script>
        // Mobile Navigation Toggle
        const navToggle = document.getElementById('navToggle');
        const mobileNav = document.getElementById('mobileNav');

        if (navToggle && mobileNav) {
            navToggle.addEventListener('click', function() {
                mobileNav.classList.toggle('open');
                const icon = navToggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-list');
                    icon.classList.toggle('bi-x-lg');
                }
            });
        }

        function toggleMobileNav() {
            if (mobileNav) {
                mobileNav.classList.remove('open');
                const icon = navToggle ? navToggle.querySelector('i') : null;
                if (icon) {
                    icon.classList.add('bi-list');
                    icon.classList.remove('bi-x-lg');
                }
            }
        }

        // FAQ Accordion
        function toggleFaq(btn) {
            const item = btn.closest('.faq-item');
            if (item) {
                item.classList.toggle('active');
            }
        }
    </script>
</body>
</html>
