<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadOpportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::with('recommendations', 'campaign')
            ->whereNotNull('opportunity_score')
            ->when($request->filled('service'), fn ($q) => $q->where('recommended_service', $request->input('service')))
            ->when($request->filled('min_score'), fn ($q) => $q->where('opportunity_score', '>=', $request->input('min_score')))
            ->orderByDesc('opportunity_score')
            ->paginate(25)
            ->withQueryString();

        $services = Lead::whereNotNull('recommended_service')->distinct()->pluck('recommended_service');

        return view('opportunities.index', compact('leads', 'services'));
    }

    public function show(Lead $lead)
    {
        $lead->load(['recommendations', 'opportunities.service', 'scans', 'analyses']);

        return view('opportunities.show', compact('lead'));
    }
}