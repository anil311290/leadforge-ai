<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\Freelancer\FreelancerSettings;
use Illuminate\Http\Request;

class FreelancerSettingsController extends Controller
{
    public function index()
    {
        $settings = FreelancerSettings::all();

        return view('freelancer.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $this->validate($request, [
            'scan_interval_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'delay_min_sec' => ['required', 'integer', 'min:0', 'max:600'],
            'delay_max_sec' => ['required', 'integer', 'min:0', 'max:600'],
            'default_include_keywords' => ['nullable', 'string'],
            'default_exclude_keywords' => ['nullable', 'string'],
            'default_exclude_countries' => ['nullable', 'string'],
            'proposal_use_ai' => ['nullable', 'boolean'],
            'profile_title' => ['required', 'string', 'max:255'],
            'profile_summary' => ['required', 'string'],
            'portfolio_url' => ['nullable', 'url'],
        ]);

        FreelancerSettings::set('scan_interval_minutes', (int) $data['scan_interval_minutes']);
        FreelancerSettings::set('delay_min_sec', (int) $data['delay_min_sec']);
        FreelancerSettings::set('delay_max_sec', (int) $data['delay_max_sec']);
        FreelancerSettings::set('default_include_keywords', $this->splitCsv($data['default_include_keywords'] ?? ''));
        FreelancerSettings::set('default_exclude_keywords', $this->splitCsv($data['default_exclude_keywords'] ?? ''));
        FreelancerSettings::set('default_exclude_countries', $this->splitCsv($data['default_exclude_countries'] ?? ''));
        FreelancerSettings::set('proposal_use_ai', $request->boolean('proposal_use_ai'));
        FreelancerSettings::set('profile_title', $data['profile_title']);
        FreelancerSettings::set('profile_summary', $data['profile_summary']);
        FreelancerSettings::set('portfolio_url', $data['portfolio_url'] ?? '');

        AuditService::record(auth()->user(), 'freelancer_settings_updated', 'FreelancerSettings');

        if ($request->wantsJson()) {
            return response()->json(['success' => 'Freelancer settings saved.']);
        }

        return back()->with('success', 'Freelancer settings saved.');
    }

    protected function splitCsv(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
