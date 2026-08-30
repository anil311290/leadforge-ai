<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyseLead;
use App\Models\Lead;
use App\Services\ActivityService;
use App\Services\AuditService;
use App\Services\Discovery\DataNormalizer;
use App\Services\Discovery\DuplicateDetector;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        protected DuplicateDetector $duplicates,
        protected ActivityService $activities
    ) {
    }

    public function index(Request $request)
    {
        $query = Lead::with('campaign', 'owner');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn ($w) => $w->where('company', 'like', "%{$q}%")
                ->orWhere('industry', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('location', 'like', "%{$q}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('score_class')) {
            $query->where('score_class', $request->input('score_class'));
        }

        $leads = $query->orderByRaw('coalesce(opportunity_score,0) DESC')->paginate(25)->withQueryString();

        $counts = [
            'all' => (int) Lead::count(),
            'new' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'qualified' => Lead::where('status', Lead::STATUS_QUALIFIED)->count(),
            'hot' => Lead::where('score_class', 'HOT')->count(),
        ];

        return view('leads.index', compact('leads', 'counts'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'company' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email'],
        ]);

        $match = $this->duplicates->findDuplicate($data);
        if ($match) {
            return back()->with('error', 'Duplicate detected — this business already exists in a lead.');
        }

        $lead = Lead::create([
            'company' => $data['company'],
            'normalized_company' => DataNormalizer::normalizeCompany($data['company']),
            'website' => $data['website'] ?? null,
            'normalized_domain' => DataNormalizer::normalizeDomain($data['website'] ?? null),
            'location' => $data['location'] ?? $data['city'] ?? null,
            'city' => $data['city'] ?? null,
            'industry' => $data['industry'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'owner_id' => auth()->id(),
            'status' => Lead::STATUS_NEW,
        ]);

        if ($data['website'] ?? null) {
            dispatch(new AnalyseLead($lead));
        }

        AuditService::record(auth()->user(), 'lead_created', 'Lead', $lead->id, null, ['company' => $lead->company]);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead created: '.$lead->company);
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);
        $lead->load(['scans.pages', 'analyses', 'recommendations', 'opportunities.service', 'emails', 'followUps', 'activities.lead', 'contacts']);

        return view('leads.show', compact('lead'));
    }

    public function importView()
    {
        return view('leads.import');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'source' => ['required', 'string'],
            'businesses' => ['nullable', 'string'],
            'csv' => ['nullable', 'file', 'mimes:csv,txt'],
        ]);

        $lines = [];

        if ($request->hasFile('csv')) {
            $path = $request->file('csv')->getRealPath();
            $lines = $this->parseCsv($path);
        } else {
            foreach (explode("\n", $request->input('businesses', '')) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $lines[] = ['name' => $line];
            }
        }

        $created = 0;
        $duplicates = 0;

        foreach ($lines as $candidate) {
            if (! $candidate['name']) {
                continue;
            }
            if ($this->duplicates->findDuplicate($candidate)) {
                $duplicates++;
                continue;
            }

            Lead::create([
                'company' => $candidate['name'],
                'normalized_company' => DataNormalizer::normalizeCompany($candidate['name']),
                'website' => $candidate['website'] ?? null,
                'normalized_domain' => DataNormalizer::normalizeDomain($candidate['website'] ?? null),
                'industry' => $candidate['industry'] ?? null,
                'location' => $candidate['location'] ?? null,
                'email' => $candidate['email'] ?? null,
                'phone' => $candidate['phone'] ?? null,
                'status' => Lead::STATUS_NEW,
                'owner_id' => auth()->id(),
            ]);
            $created++;
        }

        AuditService::record(auth()->user(), 'leads_imported', 'Lead', null, null, ['created' => $created, 'duplicates' => $duplicates]);

        return back()->with('success', "Imported {$created} leads ({$duplicates} duplicates skipped).");
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $data = $this->validate($request, [
            'status' => ['required', 'in:'.implode(',', Lead::$statuses)],
            'rejection_reason' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
        ]);

        $lead->update($data);
        AuditService::record(auth()->user(), 'lead_status_changed', 'Lead', $lead->id, null, ['status' => $data['status']]);

        return back()->with('success', 'Lead status updated to '.$data['status']);
    }

    public function addNote(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);
        $data = $this->validate($request, ['notes' => ['required', 'string']]);
        $lead->update(['notes' => $lead->notes."\n[".now()->format('d M Y H:i')."] ".$data['notes']]);

        return back()->with('success', 'Note added.');
    }

    public function claim(Lead $lead)
    {
        $this->authorize('claim', $lead);
        $lead->update(['owner_id' => auth()->id()]);

        return back()->with('success', 'Lead claimed.');
    }

    public function analyse(Lead $lead)
    {
        $this->authorize('update', $lead);
        dispatch(new AnalyseLead($lead));

        return back()->with('success', 'Analysis queued for '.$lead->company.'.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorize('update', $lead);
        AuditService::record(auth()->user(), 'lead_deleted', 'Lead', $lead->id, null, ['company' => $lead->company]);
        $lead->forceDelete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    protected function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }
            $record = array_combine($headers, $row) ?: [];
            $rows[] = [
                'name' => $record['name'] ?? $record['company'] ?? null,
                'website' => $record['website'] ?? $record['url'] ?? null,
                'email' => $record['email'] ?? null,
                'phone' => $record['phone'] ?? null,
                'industry' => $record['industry'] ?? null,
                'location' => $record['location'] ?? null,
            ];
        }
        fclose($handle);

        return $rows;
    }
}