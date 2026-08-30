<?php

namespace Database\Seeders;

use App\Models\PromptTemplate;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedUsers();
        $this->seedServices();
        $this->seedPromptTemplates();
        $this->seedSettings();
    }

    protected function seedUsers(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('123456'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'role' => 'user']
        );
    }

    protected function seedServices(): void
    {
        $catalog = [
            ['Website & E-commerce', 'Web', 'Modern responsive website with CMS and e-commerce capability.', 80000, 350000],
            ['CRM / Automation', 'Sales & Ops', 'Custom CRM with lead tracking and sales process automation.', 150000, 600000],
            ['Mobile Apps', 'Mobile', 'Native / cross-platform mobile application development.', 250000, 1200000],
            ['Custom Software', 'Software', 'Bespoke internal tools that replace manual processes.', 200000, 1000000],
            ['AI Integration', 'Innovation', 'AI-powered features: chat, document processing, predictive insights.', 150000, 800000],
            ['UI/UX Redesign', 'Design', 'User experience and visual redesign of existing web properties.', 50000, 250000],
            ['WhatsApp Automation', 'Automation', 'WhatsApp Business API automation for orders, enquiries and notifications.', 30000, 150000],
        ];

        $keywords = [
            'Website & E-commerce' => ['wordpress', 'wix', 'shopify', 'joomla', 'magento'],
            'CRM / Automation' => ['excel', 'spreadsheet', 'manual', 'register', 'ledger'],
            'Mobile Apps' => ['mobile', 'android', 'ios', 'app store'],
            'Custom Software' => ['legacy', 'offline', 'paper', 'manual process', 'outdated'],
            'AI Integration' => ['ai', 'chat', 'chatbot', 'smart', 'data'],
            'UI/UX Redesign' => ['outdated', 'basic', 'template', 'under construction'],
            'WhatsApp Automation' => ['whatsapp', 'enquiry', 'order by phone', 'call us'],
        ];

        foreach ($catalog as [$name, $category, $description, $min, $max]) {
            $service = Service::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'category' => $category, 'description' => $description, 'min_value' => $min, 'max_value' => $max, 'is_active' => true]
            );

            if ($service->rules()->count() === 0) {
                foreach ($keywords[$name] as $kw) {
                    $service->rules()->create([
                        'type' => 'positive_signal',
                        'signal' => 'tech_mention',
                        'keyword' => $kw,
                        'weight' => 15,
                    ]);
                }
            }
        }
    }

    protected function seedPromptTemplates(): void
    {
        $templates = [
            'opportunity_analysis' => "Analyse {{company}} ({{domain}}) and return JSON per instructions.",
            'email_generation' => "Write a personalised outreach email for {{company}}.",
            'follow_up' => 'Write a short follow-up email for {{company}} ({{analysis}}).',
        ];

        foreach ($templates as $key => $content) {
            $template = PromptTemplate::firstOrCreate(
                ['key' => $key],
                ['name' => ucwords(str_replace('_', ' ', $key)), 'description' => ucwords(str_replace('_', ' ', $key)).' template']
            );

            if ($template->activeVersion === null) {
                $version = $template->versions()->create(['version' => 1, 'content' => $content, 'is_active' => true]);
                $template->update(['active_version_id' => $version->id]);
            }
        }
    }

    protected function seedSettings(): void
    {
        $defaults = [
            'company_name' => 'APARK IT Solutions',
            'email_from_name' => 'APARK IT Solutions',
            'currency_symbol' => '₹',
            'email_require_approval' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['group' => 'general', 'value' => $value]);
        }
    }
}
