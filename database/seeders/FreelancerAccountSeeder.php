<?php

namespace Database\Seeders;

use App\Models\FreelancerAccount;
use Illuminate\Database\Seeder;

/**
 * Seeds the main Freelancer.com account using the configured
 * profile, project filters and proposal settings.
 *
 * OAuth token is read from FL_BOAT_OAUTH_TOKEN in .env
 * and is never hardcoded here.
 */
class FreelancerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $token = env('FL_BOAT_OAUTH_TOKEN');

        if (! $token) {
            $this->command?->warn(
                'Skipped FreelancerAccountSeeder: FL_BOAT_OAUTH_TOKEN is not set in .env.'
            );

            return;
        }

        FreelancerAccount::updateOrCreate(
            ['name' => 'Anil Prajapati'],
            [
                'oauth_token' => $token,
                'api_url' => 'https://www.freelancer.com',

                // Account status
                'is_active' => true,

                // Keep manual review enabled initially.
                // Turn this on only after proposal quality is verified.
                'auto_submit_bids' => false,

                // Bidding limits
                'max_bids_per_day' => 15,

                // Minimum project budget
                'budget_min' => 15,
                'budget_min_currency' => 'USD',

                /*
                 * Core skills and project types matching the profile:
                 *
                 * Full Stack Development
                 * PHP / Laravel / Node.js
                 * React / React Native
                 * SaaS / ERP / CRM / eCommerce
                 * APIs / Integrations
                 * Business applications
                 * Existing application development
                 */
                'include_keywords' => [
                    // Core development
                    'php',
                    'laravel',
                    'node.js',
                    'node',
                    'javascript',
                    'react',
                    'react.js',
                    'react native',
                    'expo',
                    'mysql',

                    // Backend / API
                    'rest api',
                    'api development',
                    'api integration',
                    'third party api',
                    'backend development',
                    'backend',
                    'full stack',
                    'full stack development',

                    // Web applications
                    'web application',
                    'web app',
                    'custom software',
                    'custom application',
                    'business software',
                    'business management',

                    // SaaS / Business systems
                    'saas',
                    'erp',
                    'crm',
                    'inventory management',
                    'pos',
                    'admin panel',
                    'dashboard',

                    // eCommerce
                    'ecommerce',
                    'e-commerce',
                    'ecommerce website',
                    'ecommerce app',
                    'marketplace',

                    // Payments & integrations
                    'payment gateway',
                    'payment integration',
                    'stripe',
                    'razorpay',
                    'paypal',
                    'sms integration',
                    'whatsapp integration',
                    'twilio',
                    'webhooks',
                    'authentication',

                    // Mobile
                    'mobile app',
                    'mobile application',
                    'cross platform',
                    'cross-platform',

                    // Existing application / maintenance
                    'existing application',
                    'existing codebase',
                    'existing website',
                    'feature development',
                    'bug fixing',
                    'bug fix',
                    'application modification',
                    'code modification',
                    'backend enhancement',
                    'performance optimization',

                    // Deployment / infrastructure
                    'aws',
                    'aws ec2',
                    'linux',
                    'ubuntu',
                    'nginx',
                    'pm2',
                    'docker',
                    'deployment',
                    'server deployment',
                    'production deployment',

                    // AI / automation
                    'ai integration',
                    'openai',
                    'gpt',
                    'llm',
                    'chatbot',
                    'automation',
                ],

                /*
                 * Projects that are outside the preferred profile.
                 */
                'exclude_keywords' => [
                    // Adult / gambling
                    'adult',
                    'porn',
                    'pornography',
                    'betting',
                    'gambling',
                    'casino',
                    'lottery',

                    // Crypto / high-risk trading
                    'crypto',
                    'cryptocurrency',
                    'forex',
                    'trading bot',
                    'nft',
                    'blockchain',
                    'solidity',

                    // Website builders / platforms not preferred
                    'wordpress',
                    'wix',
                    'shopify',

                    // Game development
                    'unity',
                    'game development',
                    'game developer',

                    // Technologies outside current positioning
                    'flutter',
                ],

                // Do not exclude clients based on country.
                'exclude_countries' => [],

                // Default proposal settings
                'bid_amount_default' => 35,
                'bid_period_days_default' => 5,

                // Generic fallback link, only used when no specific project below matches the client's ask.
                'portfolio_url' => 'https://logiclooms.in',

                /*
                 * Past-work projects for portfolio matching. Public URLs only —
                 * never store admin panel links or credentials here.
                 * demo_admin_url/demo_credentials are optional, non-production demo
                 * logins only, shared in a proposal only when a client explicitly
                 * asks to test/access the admin panel.
                 */
                'portfolio_projects' => [
                    ['title' => 'Pharmiza', 'url' => 'https://dev.pharmiza.in/', 'tags' => ['healthcare', 'medicine', 'manufacturer', 'laravel'], 'description' => 'Health care medicine manufacturer website', 'demo_admin_url' => 'https://dev.pharmiza.in/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Bigul', 'url' => 'https://bigul.co/', 'tags' => ['trading', 'nse', 'bse', 'ekyc', 'kyc', 'payment gateway', 'fintech', 'laravel'], 'description' => 'Trading website with admin panel, eKYC journey and third-party API integrations (NSE, BSE, payment gateways)', 'demo_admin_url' => '', 'demo_credentials' => ''],
                    ['title' => 'Hospital Data Management System', 'url' => 'https://devw.testproject.in/public', 'tags' => ['healthcare', 'hospital', 'api', 'laravel'], 'description' => "Software to manage a hospital system's data, including APIs for a mobile app", 'demo_admin_url' => 'https://devw.testproject.in/public', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Loan Management System', 'url' => 'https://mpbcdc.testproject.in/login', 'tags' => ['loan', 'fintech', 'api', 'laravel'], 'description' => 'Software to manage beneficiary loan data and APIs for a mobile app', 'demo_admin_url' => 'https://mpbcdc.testproject.in/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Employee KYC Enrollment', 'url' => 'https://enrollment.testproject.in/login', 'tags' => ['kyc', 'api', 'laravel'], 'description' => 'Software to manage employee KYC data and APIs for a mobile app', 'demo_admin_url' => 'https://enrollment.testproject.in/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'DoctorBuddy', 'url' => 'https://doctorbuddy.in/', 'tags' => ['healthcare', 'appointment', 'booking', 'laravel'], 'description' => 'Doctor and medical representative (MR) appointment booking system', 'demo_admin_url' => 'https://doctorbuddy.in/', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'EventBus', 'url' => 'https://eventbus.logiclooms.in/', 'tags' => ['event', 'booking', 'laravel'], 'description' => 'Event booking system', 'demo_admin_url' => 'https://eventbus.logiclooms.in/admin', 'demo_credentials' => 'admin: admin@gmail.com / admin@123#* — operator: maasharda@yopmail.com / 123456'],
                    ['title' => 'Interior Design Website', 'url' => 'https://interior.logiclooms.in/', 'tags' => ['interior design', 'cms', 'laravel'], 'description' => 'Interior design website with admin panel', 'demo_admin_url' => 'https://interior.logiclooms.in/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'College Management System', 'url' => 'https://college.logiclooms.in', 'tags' => ['education', 'college', 'erp', 'codeigniter'], 'description' => 'College management software with website', 'demo_admin_url' => 'https://college.logiclooms.in/admin-login', 'demo_credentials' => 'admin / 123456'],
                    ['title' => 'Gym Product Store', 'url' => 'https://gymproduct.logiclooms.in/', 'tags' => ['ecommerce', 'gym', 'payment gateway', 'laravel'], 'description' => 'E-commerce gym product website with payment gateway integration', 'demo_admin_url' => 'https://gymproduct.logiclooms.in/login', 'demo_credentials' => 'admin / 123456'],
                    ['title' => 'QuickPrint', 'url' => 'https://quickprint.logiclooms.in/login', 'tags' => ['invoicing', 'excel', 'laravel'], 'description' => 'Upload Excel and print invoice/receipt tool', 'demo_admin_url' => 'https://quickprint.logiclooms.in/login', 'demo_credentials' => 'admin@gmail.com / admin@123#*'],
                    ['title' => 'School Management System', 'url' => 'https://school.logiclooms.in/', 'tags' => ['education', 'school', 'erp', 'codeigniter'], 'description' => 'School management ERP system', 'demo_admin_url' => 'https://school.logiclooms.in/authentication', 'demo_credentials' => 'admin@admin.com / admin@admin.com'],
                    ['title' => 'Property Builder Website', 'url' => 'https://properties.logiclooms.in/', 'tags' => ['real estate', 'property', 'laravel'], 'description' => 'Property builder website including admin panel', 'demo_admin_url' => 'https://properties.logiclooms.in/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Tutora Job Portal', 'url' => 'https://tutora.logiclooms.in/', 'tags' => ['job portal', 'recruitment', 'social login', 'payment gateway', 'codeigniter'], 'description' => 'Job portal website with social media login and payment gateway integration', 'demo_admin_url' => 'https://tutora.logiclooms.in/admin-login', 'demo_credentials' => 'admin / admin@123#*'],
                    ['title' => 'Freedom From Diabetes', 'url' => 'https://www.freedomfromdiabetes.org/', 'tags' => ['healthcare', 'cms', 'laravel'], 'description' => 'Dynamic informational website', 'demo_admin_url' => 'https://www.freedomfromdiabetes.org/', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Ecommerce Platform', 'url' => 'https://ecommerce.logiclooms.in/', 'tags' => ['ecommerce', 'payment gateway', 'laravel'], 'description' => 'Ecommerce website with admin panel and multiple payment gateway integrations', 'demo_admin_url' => 'https://ecommerce.logiclooms.in/admin/login', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'GetJourney', 'url' => 'https://getjourney.logiclooms.in/', 'tags' => ['tours', 'travel', 'laravel'], 'description' => 'Tours and travel booking website with admin panel', 'demo_admin_url' => 'https://getjourney.logiclooms.in/login', 'demo_credentials' => 'admin@gmail.com / Admin@123#*'],
                    ['title' => 'Parlour Website', 'url' => 'https://parlour.hrrswai.com/', 'tags' => ['salon', 'booking', 'zend'], 'description' => 'Parlour website including admin panel', 'demo_admin_url' => 'https://parlour.hrrswai.com/login', 'demo_credentials' => 'admin / admin123'],
                    ['title' => 'TalentStack', 'url' => 'https://talentstack.aaochaletaxi.com/', 'tags' => ['recruitment', 'hr', 'codeigniter'], 'description' => 'Recruitment management system', 'demo_admin_url' => 'https://talentstack.aaochaletaxi.com/admin', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Insurance CRM', 'url' => 'https://insurance.adsinfotech.biz/', 'tags' => ['crm', 'insurance', 'laravel'], 'description' => 'CRM software for insurance', 'demo_admin_url' => 'https://insurance.adsinfotech.biz/admin', 'demo_credentials' => 'admin@gmail.com / 123456'],
                    ['title' => 'Qonsistic', 'url' => 'http://qonsistic.aaochaletaxi.com/', 'tags' => ['crm', 'sales automation', 'ai', 'stripe', 'automation', 'n8n', 'laravel'], 'description' => 'AI-powered CRM and sales automation platform: leads, follow-ups, pipelines, proposals, meetings, Stripe payments, n8n automation', 'demo_admin_url' => 'http://qonsistic.aaochaletaxi.com/admin', 'demo_credentials' => 'superadmin@gmail.com / 123456 (customer demo: customer@gmail.com / 123456)'],
                    ['title' => 'NexoraCRM', 'url' => 'https://nexoracrm.aaochaletaxi.com/', 'tags' => ['crm', 'business management', 'codeigniter'], 'description' => 'All-in-one business management system: customers, leads, projects, tasks, invoices, payments, estimates, support tickets, reports', 'demo_admin_url' => 'https://nexoracrm.aaochaletaxi.com/admin', 'demo_credentials' => 'admin@nexoracrm.com / admin123 (any customer email / admin123)'],
                    ['title' => 'DoDid', 'url' => 'https://dev-dodid.sahrudaya.online/', 'tags' => ['task management', 'ai', 'saas', 'laravel'], 'description' => 'AI-integrated task management platform with recurring work, deadlines and reminders; mobile app available', 'demo_admin_url' => '', 'demo_credentials' => ''],
                    ['title' => 'Reco', 'url' => 'https://reco.aaochaletaxi.com/', 'tags' => ['accounting', 'saas', 'razorpay', 'gst', 'laravel'], 'description' => 'Accounting SaaS: invoices, income, expenses, receivables, payables, vouchers, bank accounts, GST reports, Razorpay integrated, mobile app available', 'demo_admin_url' => 'https://reco.aaochaletaxi.com/admin', 'demo_credentials' => 'admin@mail.com / 123456'],
                    ['title' => 'ManuFlowCRM', 'url' => 'https://manuflow.aaochaletaxi.com/authentication/login', 'tags' => ['manufacturing', 'crm', 'codeigniter'], 'description' => 'Manufacturing business management: materials, customers, leads, projects, tasks, invoices, payments, estimates, support tickets, reports', 'demo_admin_url' => 'https://manuflow.aaochaletaxi.com/admin/authentication', 'demo_credentials' => 'admin@manuflow.com / admin123 (customer demo: any customer email / admin123)'],
                ],

                'status' => 'pending',
            ]
        );

        $this->command?->info(
            'Freelancer account settings seeded successfully.'
        );
    }
}