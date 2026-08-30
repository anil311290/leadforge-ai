<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use Illuminate\Http\Request;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $logs = AiUsageLog::with(['user', 'lead'])
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->input('provider')))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $totals = [
            'requests' => (int) AiUsageLog::count(),
            'total_cost' => (float) AiUsageLog::sum('cost'),
            'avg_duration_ms' => (int) round(AiUsageLog::avg('duration_ms') ?? 0),
            'prompts' => AiUsageLog::select('prompt_name')->groupBy('prompt_name')->get(),
        ];

        $byDay = AiUsageLog::selectRaw('date(created_at) as day, count(*) total, coalesce(sum(cost),0) cost')
            ->groupBy('day')->orderBy('day')->get();

        return view('ai.usage', compact('logs', 'totals', 'byDay'));
    }
}