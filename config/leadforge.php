<?php
return [
    'product' => 'LeadForge AI',
    'full_name' => 'LeadForge AI — Project Discovery & Sales Intelligence',
    'tagline' => 'Find the Right Business. Discover the Right Project.',
    'owner' => 'APARK IT Solutions',
    'currency' => env('LF_CURRENCY', 'INR'),
    'currency_symbol' => env('LF_CURRENCY_SYMBOL', '₹'),

    /*
    | Discovery sources. Compliant / public only. No bypass behaviour.
    */
    'discovery' => [
        'providers' => [
            'search_api' => 'App\\Services\\Discovery\\Providers\\SearchApiDiscoveryProvider',
            'manual_urls' => 'App\\Services\\Discovery\\Providers\\ManualUrlDiscoveryProvider',
            'csv' => 'App\\Services\\Discovery\\Providers\\CsvDiscoveryProvider',
            'ai_web_search' => 'App\\Services\\Discovery\\Providers\\AiWebSearchProvider',
        ],
        'enabled_providers' => ['manual_urls', 'csv', 'ai_web_search'],
        'search_api' => [
            'endpoint' => env('LF_DISCOVERY_ENDPOINT'),
            'api_key' => env('LF_DISCOVERY_API_KEY'),
            'default_query' => env('LF_DISCOVERY_QUERY', 'software companies'),
        ],
    ],

    // Crawler limits (Python worker)
    'crawler' => [
        'max_pages' => env('LF_CRAWLER_MAX_PAGES', 10),
        'timeout' => env('LF_CRAWLER_TIMEOUT', 15),
        'max_response_size_kb' => env('LF_CRAWLER_MAX_RESPONSE_KB', 4096),
        'rate_limit_per_second' => env('LF_CRAWLER_RATE_LIMIT', 1),
        'max_retries' => env('LF_CRAWLER_MAX_RETRIES', 3),
    ],

    // AI provider
    'ai' => [
        'provider' => env('LF_AI_PROVIDER', 'openai'),
        'model' => env('LF_AI_MODEL', 'gpt-4o-mini'),
        'api_key' => env('LF_AI_API_KEY'),
        'base_url' => env('LF_AI_BASE_URL'),
        'timeout' => env('LF_AI_TIMEOUT', 60),
        'max_tokens' => env('LF_AI_MAX_TOKENS', 2000),
        'temperature' => env('LF_AI_TEMPERATURE', 0.4),
        'cost_per_1k_input' => env('LF_AI_COST_1K_INPUT', 0.0),
        'cost_per_1k_output' => env('LF_AI_COST_1K_OUTPUT', 0.0),
    ],

    // Email
    'email' => [
        'provider' => env('LF_EMAIL_PROVIDER', 'gmail'),
        'from_name' => env('LF_EMAIL_FROM_NAME', 'APARK IT Solutions'),
        'from_email' => env('LF_EMAIL_FROM_EMAIL'),
        'require_approval' => env('LF_EMAIL_REQUIRE_APPROVAL', true),
        'default_send_hour' => env('LF_EMAIL_DEFAULT_SEND_HOUR', 9),
    ],

    // Follow-up sequence (days)
    'followup' => [
        'days' => [0, 3, 7, 14, 30],
        'stop_on' => ['REPLIED', 'INTERESTED', 'MEETING', 'NOT_INTERESTED', 'DO_NOT_CONTACT', 'WON', 'LOST'],
    ],

    // Opportunity scoring weights (total 100)
    'scoring' => [
        'business_fit' => 20,
        'pain_gap' => 20,
        'missing_capability' => 20,
        'company_potential' => 15,
        'technology_gap' => 10,
        'contact_availability' => 5,
        'project_potential' => 5,
        'ai_confidence' => 5,
    ],

    // Default service value ranges (configurable via services table)
    'project_values' => [
        'Website Development' => [50000, 150000],
        'Website Redesign' => [40000, 120000],
        'E-commerce' => [100000, 400000],
        'CRM' => [100000, 500000],
        'ERP' => [300000, 1500000],
        'Inventory Management' => [80000, 300000],
        'Dealer Portal' => [150000, 500000],
        'Customer Portal' => [100000, 400000],
        'Transport Management' => [150000, 500000],
        'Fleet Management' => [100000, 400000],
        'Billing' => [50000, 200000],
        'Mobile App' => [200000, 800000],
        'API Integration' => [25000, 200000],
        'WhatsApp Automation' => [30000, 150000],
        'AI Automation' => [50000, 300000],
        'Business Process Automation' => [50000, 300000],
        'Maintenance' => [30000, 120000],
        'SEO/Performance' => [25000, 100000],
        'Custom Software' => [100000, 600000],
    ],
];