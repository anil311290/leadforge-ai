<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\Task;
use App\Models\Activity;
use App\Models\Campaign;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_leads' => Lead::count(),
            'new_leads' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'analysed_leads' => Lead::whereNotNull('analyzed_at')->count(),
            'hot_leads' => Lead::where('score_class', 'HOT')->count(),
            'high_leads' => Lead::where('score_class', 'HIGH')->count(),
            'pending_email_approval' => EmailMessage::where('status', 'pending_approval')->count(),
            'emails_sent' => EmailMessage::where('direction', 'outbound')->whereNotNull('sent_at')->count(),
            'replies_received' => EmailMessage::where('direction', 'inbound')->count(),
            'follow_ups_due_today' => FollowUp::where('status', 'pending')
                ->whereDate('scheduled_at', today())->count(),
            'meetings' => Lead::where('status', Lead::STATUS_MEETING)->count(),
            'proposals' => Lead::where('status', Lead::STATUS_PROPOSAL)->count(),
            'won' => Lead::where('status', Lead::STATUS_WON)->count(),
            'lost' => Lead::where('status', Lead::STATUS_LOST)->count(),
            'pipeline_value' => (float) Lead::open()->sum('estimated_max'),
            'won_revenue' => (float) Lead::where('status', Lead::STATUS_WON)->sum('estimated_max'),
        ];

        $qualified = (float) Lead::whereIn('status', [
            Lead::STATUS_CONTACTED, Lead::STATUS_REPLIED, Lead::STATUS_INTERESTED,
            Lead::STATUS_MEETING, Lead::STATUS_PROPOSAL, Lead::STATUS_NEGOTIATION, Lead::STATUS_WON,
        ])->count();

        $stats['conversion_rate'] = $qualified > 0 ? round(($stats['won'] / $qualified) * 100, 1) : 0;
        $stats['win_rate'] = ($stats['won'] + $stats['lost']) > 0
            ? round(($stats['won'] / ($stats['won'] + $stats['lost'])) * 100, 1)
            : 0;

        $topOpportunities = Lead::whereNotNull('opportunity_score')
            ->orderByDesc('opportunity_score')
            ->orderByDesc('estimated_max')
            ->limit(8)
            ->get();

        $recentActivities = Activity::with('lead', 'user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $pipelineBreakdown = Lead::selectRaw('status, count(*) as total')
            ->groupBy('status')->get()->pluck('total', 'status');

        $byService = Lead::whereNotNull('recommended_service')
            ->selectRaw('recommended_service, count(*) as total')
            ->groupBy('recommended_service')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact('stats', 'topOpportunities', 'recentActivities', 'pipelineBreakdown', 'byService'));
    }
}