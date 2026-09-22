<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class WebsiteBuilderUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'websitebuilder@example.com'],
            [
                'name' => 'Website Builder',
                'password' => 'password',
                'role' => 'website_builder',
                'is_active' => true,
            ]
        );
    }
}
