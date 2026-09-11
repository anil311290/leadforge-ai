<?php

return [
    // Default Freelancer.com API base (per-account api_url overrides this).
    'api_url' => env('FL_API_URL', 'https://www.freelancer.com'),

    // How often (minutes) the scheduler scans each active account for new projects.
    'scan_interval_minutes' => env('FL_SCAN_INTERVAL_MINUTES', 15),

    // Safety pacing between placing consecutive bids for the same account.
    'delay_min_sec' => env('FL_DELAY_MIN_SEC', 5),
    'delay_max_sec' => env('FL_DELAY_MAX_SEC', 15),

    // Default filters applied when an account doesn't override them.
    'default_include_keywords' => array_filter(array_map('trim', explode(',', env('FL_INCLUDE_KEYWORDS', 'php,laravel,node.js,javascript,react,python,rest api,mysql,api integration')))),
    'default_exclude_keywords' => array_filter(array_map('trim', explode(',', env('FL_EXCLUDE_KEYWORDS', 'adult,betting,gambling')))),
    'default_exclude_countries' => array_filter(array_map('trim', explode(',', env('FL_EXCLUDE_COUNTRIES', '')))),

    // Proposal generation. Falls back to a template when AI is not configured.
    'proposal' => [
        'use_ai' => env('FL_PROPOSAL_USE_AI', true),
        'profile_title' => env('FL_PROFILE_TITLE', 'Full Stack Developer (PHP/Laravel/Node.js)'),
        'profile_summary' => env('FL_PROFILE_SUMMARY', 'Experienced building SaaS, ERP, CRM and eCommerce platforms with PHP, Laravel, Node.js and MySQL, integrating APIs, payment gateways and AI-powered automation.'),
        // Only mentioned in a proposal when the project explicitly asks for a portfolio/past work link.
        'portfolio_url' => env('FL_PORTFOLIO_URL', 'https://logiclooms.in'),
    ],
];
