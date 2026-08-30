<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index()
    {
        $columns = [
            Lead::STATUS_NEW => 'New',
            Lead::STATUS_QUALIFIED => 'Qualified',
            Lead::STATUS_CONTACTED => 'Contacted',
            Lead::STATUS_REPLIED => 'Replied',
            Lead::STATUS_INTERESTED => 'Interested',
            Lead::STATUS_MEETING => 'Meeting',
            Lead::STATUS_PROPOSAL => 'Proposal',
            Lead::STATUS_NEGOTIATION => 'Negotiation',
            Lead::STATUS_WON => 'Won',
            Lead::STATUS_LOST => 'Lost',
        ];

        $leadsByStatus = Lead::with('owner')
            ->orderByDesc('opportunity_score')
            ->get()
            ->groupBy('status');

        return view('pipeline.index', compact('columns', 'leadsByStatus'));
    }

    public function move(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);
        $data = $this->validate($request, ['status' => ['required', 'in:'.implode(',', Lead::$statuses)]]);

        $lead->update(['status' => $data['status']]);
        AuditService::record(auth()->user(), 'pipeline_moved', 'Lead', $lead->id, null, ['status' => $data['status']]);

        return redirect()->route('pipeline.index')->with('success', $lead->company.' moved to '.$data['status']);
    }
}