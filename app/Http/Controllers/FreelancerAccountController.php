<?php

namespace App\Http\Controllers;

use App\Jobs\ScanFreelancerAccount;
use App\Models\FreelancerAccount;
use App\Services\AuditService;
use App\Services\Freelancer\FreelancerApiClient;
use Illuminate\Http\Request;

class FreelancerAccountController extends Controller
{
    public function index()
    {
        $accounts = FreelancerAccount::withCount('bids')->orderBy('name')->get();

        return view('freelancer.accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('freelancer.accounts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $account = FreelancerAccount::create($data + ['user_id' => auth()->id()]);
        AuditService::record(auth()->user(), 'freelancer_account_created', 'FreelancerAccount', $account->id, null, ['name' => $account->name]);

        return redirect()->route('freelancer.accounts.index')->with('success', 'Freelancer account added: '.$account->name);
    }

    public function edit(FreelancerAccount $account)
    {
        return view('freelancer.accounts.edit', compact('account'));
    }

    public function update(Request $request, FreelancerAccount $account)
    {
        $data = $this->validatedData($request, requireToken: false);

        if (empty($data['oauth_token'])) {
            unset($data['oauth_token']); // keep existing key when left blank
        }

        $account->update($data);
        AuditService::record(auth()->user(), 'freelancer_account_updated', 'FreelancerAccount', $account->id);

        return redirect()->route('freelancer.accounts.index')->with('success', 'Freelancer account updated: '.$account->name);
    }

    public function destroy(FreelancerAccount $account)
    {
        $name = $account->name;
        $account->delete();
        AuditService::record(auth()->user(), 'freelancer_account_deleted', 'FreelancerAccount', null, ['name' => $name]);

        return back()->with('success', "Freelancer account removed: {$name}");
    }

    public function testConnection(FreelancerAccount $account)
    {
        try {
            $user = (new FreelancerApiClient($account))->getSelfUser();
            $account->update(['status' => 'connected', 'last_error' => null]);

            return response()->json(['success' => 'Connected as '.($user['username'] ?? $user['display_name'] ?? 'unknown user')]);
        } catch (\Throwable $e) {
            $account->update(['status' => 'error', 'last_error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function scanNow(FreelancerAccount $account)
    {
        try {
            ScanFreelancerAccount::dispatch($account->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not start the scan: '.$e->getMessage());
        }

        return back()->with('success', "Scan queued for {$account->name}.");
    }

    protected function validatedData(Request $request, bool $requireToken = true): array
    {
        $data = $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'freelancer_username' => ['nullable', 'string', 'max:255'],
            'oauth_token' => [$requireToken ? 'required' : 'nullable', 'string'],
            'api_url' => ['nullable', 'url'],
            'is_active' => ['nullable', 'boolean'],
            'auto_submit_bids' => ['nullable', 'boolean'],
            'max_bids_per_day' => ['nullable', 'integer', 'min:1', 'max:200'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_min_currency' => ['nullable', 'string', 'max:8'],
            'include_keywords' => ['nullable', 'string'],
            'exclude_keywords' => ['nullable', 'string'],
            'exclude_countries' => ['nullable', 'string'],
            'bid_amount_default' => ['nullable', 'numeric', 'min:0'],
            'bid_period_days_default' => ['nullable', 'integer', 'min:1', 'max:90'],
            'proposal_use_ai' => ['nullable', 'in:0,1'],
            'profile_title' => ['nullable', 'string', 'max:255'],
            'profile_summary' => ['nullable', 'string'],
            'portfolio_url' => ['nullable', 'url'],
            'portfolio_projects' => ['nullable', 'string'],
        ]);

        $data['api_url'] = $data['api_url'] ?? 'https://www.freelancer.com';
        $data['is_active'] = $request->boolean('is_active', true);
        $data['auto_submit_bids'] = $request->boolean('auto_submit_bids', false);
        $data['include_keywords'] = $this->splitCsv($data['include_keywords'] ?? '');
        $data['exclude_keywords'] = $this->splitCsv($data['exclude_keywords'] ?? '');
        $data['exclude_countries'] = $this->splitCsv($data['exclude_countries'] ?? '');
        // Empty selection means "use the global default" (stored as null, not false).
        $data['proposal_use_ai'] = $request->filled('proposal_use_ai') ? $request->boolean('proposal_use_ai') : null;
        $data['profile_title'] = $data['profile_title'] ?? null;
        $data['profile_summary'] = $data['profile_summary'] ?? null;
        $data['portfolio_url'] = $data['portfolio_url'] ?? null;
        $data['portfolio_projects'] = \App\Models\FreelancerAccount::parsePortfolioProjects($data['portfolio_projects'] ?? '');

        return $data;
    }

    protected function splitCsv(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
