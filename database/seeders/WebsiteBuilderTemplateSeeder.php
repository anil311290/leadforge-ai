<?php

namespace Database\Seeders;

use App\Models\WebsiteTemplate;
use Illuminate\Database\Seeder;

class WebsiteBuilderTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'Modern Business', 'slug' => 'modern-business', 'description' => 'Modern business style', 'category' => 'general'],
            ['name' => 'Professional', 'slug' => 'professional', 'description' => 'Professional business style', 'category' => 'general'],
            ['name' => 'Medical', 'slug' => 'medical', 'description' => 'Healthcare business style', 'category' => 'medical'],
            ['name' => 'Restaurant', 'slug' => 'restaurant', 'description' => 'Food and dining brand', 'category' => 'restaurant'],
            ['name' => 'Salon', 'slug' => 'salon', 'description' => 'Beauty and personal care', 'category' => 'salon'],
        ];

        foreach ($templates as $template) {
            WebsiteTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
