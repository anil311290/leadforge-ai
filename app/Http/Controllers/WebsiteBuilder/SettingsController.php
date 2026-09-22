<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'website_base_url' => Setting::get('website_base_url', env('APP_URL', 'http://localhost')),
            'default_template' => Setting::get('website_default_template', 'modern-business'),
            'default_ai_provider' => Setting::get('website_default_ai_provider', 'openai'),
            'default_whatsapp_message' => Setting::get('website_default_whatsapp_message', 'Hi {business_name}, I created a demo website for your business. You can preview it here: {website_url}'),
        ];

        return view('website-builder.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'website_base_url' => ['nullable', 'url'],
            'default_template' => ['nullable', 'string'],
            'default_ai_provider' => ['nullable', 'string'],
            'default_whatsapp_message' => ['nullable', 'string'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set('website_'.$key, $value, 'website_builder');
        }

        return back()->with('success', 'Website Builder settings saved.');
    }
}
