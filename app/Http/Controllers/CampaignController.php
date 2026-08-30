<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Lead;
use App\Services\AuditService;
use App\Services\Discovery\DiscoveryEngine;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::withCount(['leads as leads_count'])
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('campaigns.index', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'min_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_businesses' => ['nullable', 'integer', 'min:1', 'max:500'],
            'email_outreach_enabled' => ['nullable', 'boolean'],
            'auto_analysis_enabled' => ['nullable', 'boolean'],
        ]);

        $campaign = Campaign::create([
            'user_id' => auth()->id(),
            'name' => $data['name'] ?? 'Discovery — '.$data['location'],
            'location' => $data['location'],
            'radius_km' => $data['radius_km'] ?? null,
            'min_score' => $data['min_score'] ?? 0,
            'max_businesses' => $data['max_businesses'] ?? null,
            'email_outreach_enabled' => $request->boolean('email_outreach_enabled'),
            'auto_analysis_enabled' => $request->boolean('auto_analysis_enabled'),
            'status' => 'running',
            'started_at' => now(),
        ]);

        AuditService::record(auth()->user(), 'campaign_started', 'Campaign', $campaign->id, null, ['location' => $data['location']]);

        // Dispatch discovery engine to run asynchronously
        if (config('queue.default') !== 'sync') {
            DiscoveryEngine::start($campaign);
        } else {
            (new DiscoveryEngine())->runForCampaign($campaign);
        }

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign started. Discovery is running in the background.');
    }

    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        $stats = [
            'businesses_discovered' => $campaign->leads()->count(),
            'websites_found' => $campaign->leads()->whereNotNull('website')->count(),
            'websites_scanned' => $campaign->leads()->whereHas('scans')->count(),
            'analysed' => $campaign->leads()->whereNotNull('analyzed_at')->count(),
            'qualified' => $campaign->leads()->whereIn('status', ['QUALIFIED','QUALIFIED'])->count(),
            'hot' => $campaign->leads()->whereIn('score_class', ['HOT','HIGH'])->count(),
            'pipeline' => (float) $campaign->leads()->sum('estimated_max'),
        ];

        $leads = $campaign->leads()->orderByDesc('opportunity_score')->paginate(12);

        return view('campaigns.show', compact('campaign', 'stats', 'leads'));
    }

    public function showCreate()
    {
        return view('campaigns.create');
    }

    public function edit()
    {
        # handled via show; included for completeness
    }

    public function pause(Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        $campaign->update(['status' => 'paused']);
        AuditService::record(auth()->user(), 'campaign_paused', 'Campaign', $campaign->id);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        $campaign->update(['status' => 'running']);
        AuditService::record(auth()->user(), 'campaign_resumed', 'Campaign', $campaign->id);

        if (config('app.env') !== 'testing' && config('queue.default') === 'sync') {
            (new DiscoveryEngine())->start($campaign);
        }

        return back()->with('success', 'Campaign resumed.');
    }

    public function cancel(Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        $campaign->update(['status' => 'failed', 'completed_at' => now(), 'error' => 'Cancelled by user']);
        AuditService::record(auth()->user(), 'campaign_cancelled', 'Campaign', $campaign->id);

        return back()->with('success', 'Campaign cancelled.');
    }
}