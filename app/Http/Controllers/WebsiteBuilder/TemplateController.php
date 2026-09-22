<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = [
            ['name' => 'Modern Business', 'slug' => 'modern-business'],
            ['name' => 'Professional', 'slug' => 'professional'],
            ['name' => 'Medical', 'slug' => 'medical'],
            ['name' => 'Restaurant', 'slug' => 'restaurant'],
            ['name' => 'Salon', 'slug' => 'salon'],
        ];

        return view('website-builder.templates', compact('templates'));
    }
}
