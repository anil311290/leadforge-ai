<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Services\AuditService;
use App\Services\FollowUp\FollowUpEngine;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $followUps = FollowUp::with(['lead' => fn ($q) => $q->withTrashed()])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByRaw("case when status='pending' and scheduled_at<=now() then 0 when status='pending' then 1 else 2 end")
            ->orderBy('scheduled_at')
            ->paginate(20);

        $summary = [
            'overdue' => FollowUp::where('status', 'pending')->where('scheduled_at', '<=', now())->count(),
            'scheduled' => FollowUp::where('status', 'pending')->where('scheduled_at', '>', now())->count(),
            'sent' => FollowUp::where('status', 'sent')->count(),
        ];

        return view('followups.index', compact('followUps', 'summary'));
    }

    public function complete(FollowUp $followUp)
    {
        $followUp->update(['status' => 'cancelled']);
        AuditService::record(auth()->user(), 'followup_completed', 'FollowUp', $followUp->id);

        return back()->with('success', 'Follow-up marked completed.');
    }

    /**
     * Manually trigger a follow-up for a lead.
     */
    public function trigger(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        FollowUpEngine::scheduleNext((int) $lead->id);

        AuditService::record(auth()->user(), 'followup_triggered', 'Lead', $lead->id, null, ['company' => $lead->company]);

        return back()->with('success', 'Follow-up scheduled for '.$lead->company.'.');
    }
}