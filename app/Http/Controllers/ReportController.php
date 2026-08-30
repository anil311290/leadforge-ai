<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->filled('from') ? \Carbon\Carbon::parse($request->input('from')) : now()->subDays(30)->startOfDay();
        $end = $request->filled('to') ? \Carbon\Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();

        $kpis = [
            'discovered' => Lead::whereBetween('created_at', [$start, $end])->count(),
            'qualified' => Lead::whereNotNull('opportunity_score')->count(),
            'emails_sent' => EmailMessage::where('direction', 'outbound')->whereNotNull('sent_at')->whereBetween('created_at', [$start, $end])->count(),
            'replies' => EmailMessage::where('direction', 'inbound')->whereBetween('created_at', [$start, $end])->count(),
            'meetings' => Lead::where('status', Lead::STATUS_MEETING)->count(),
            'proposals' => Lead::where('status', Lead::STATUS_PROPOSAL)->count(),
            'won' => Lead::where('status', Lead::STATUS_WON)->count(),
            'revenue' => (float) Lead::where('status', Lead::STATUS_WON)->sum('estimated_max'),
        ];

        $funnel = [
            'Discovered' => Lead::whereIn('status', [Lead::STATUS_DISCOVERED, Lead::STATUS_NEW])->count(),
            'Qualified' => Lead::where('status', Lead::STATUS_QUALIFIED)->count(),
            'Contacted' => Lead::whereIn('status', [Lead::STATUS_CONTACTED, Lead::STATUS_REPLIED, Lead::STATUS_INTERESTED])->count(),
            'Meeting' => Lead::where('status', Lead::STATUS_MEETING)->count(),
            'Proposal' => Lead::where('status', Lead::STATUS_PROPOSAL)->count(),
            'Negotiation' => Lead::where('status', Lead::STATUS_NEGOTIATION)->count(),
            'Won' => Lead::where('status', Lead::STATUS_WON)->count(),
        ];

        $byService = Lead::whereNotNull('recommended_service')
            ->select('recommended_service', DB::raw('count(*) total'), DB::raw('coalesce(avg(opportunity_score),0) avg_score'))
            ->groupBy('recommended_service')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $emailsOverTime = EmailMessage::whereBetween('created_at', [$start, $end])
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byScoreClass = Lead::selectRaw('score_class, count(*) total')->whereNotNull('score_class')->groupBy('score_class')->get();

        return view('reports.index', compact('kpis', 'funnel', 'byService', 'emailsOverTime', 'byScoreClass', 'start', 'end'));
    }
}