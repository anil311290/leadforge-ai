<?php

namespace App\Services\Freelancer;

use App\Models\FreelancerAccount;
use App\Models\FreelancerBid;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates one scan-and-bid cycle for a single Freelancer.com account:
 * fetch active projects, filter, generate a proposal, and either submit the
 * bid automatically or save it as a draft awaiting manual approval.
 */
class BidPlacementService
{
    public function __construct(
        protected BidEligibilityService $eligibility,
        protected ProposalGenerator $proposals,
        protected BidQuoteCalculator $quotes,
    ) {
    }

    public function scanAndBid(FreelancerAccount $account): array
    {
        $summary = ['scanned' => 0, 'eligible' => 0, 'submitted' => 0, 'drafted' => 0, 'skipped' => 0, 'errors' => 0];

        $client = new FreelancerApiClient($account);

        try {
            $client->getSelfUser();
            $account->status = 'connected';
            $account->last_error = null;
        } catch (\Throwable $e) {
            $account->status = 'error';
            $account->last_error = $e->getMessage();
            $account->save();
            Log::warning('Freelancer account auth failed', ['account_id' => $account->id, 'error' => $e->getMessage()]);

            $summary['auth_failed'] = true;
            $summary['error'] = $e->getMessage();

            return $summary;
        }

        $remainingToday = max(0, $account->max_bids_per_day - $account->todayBidCount());

        if ($remainingToday <= 0) {
            $account->last_scanned_at = now();
            $account->save();
            $summary['limit_reached'] = true;
            $summary['max_bids_per_day'] = $account->max_bids_per_day;

            return $summary;
        }

        $searchFilters = [
            'sort_field' => 'time_updated',
            'project_statuses' => ['open'],
        ];

        $projects = [];

        // 1. Try keyword-based search if the account specifies include_keywords
        if (! empty($account->include_keywords)) {
            $keywords = array_values(array_filter((array) $account->include_keywords));
            if (! empty($keywords)) {
                $sampleQuery = implode(' ', array_slice($keywords, 0, 3));
                if ($sampleQuery !== '') {
                    try {
                        $projects = $client->searchActiveProjects(array_merge($searchFilters, ['query' => $sampleQuery]), limit: max(20, $remainingToday * 3));
                    } catch (\Throwable $e) {
                        Log::info('Freelancer keyword search query failed, falling back to general search', ['account_id' => $account->id, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        // 2. Fallback to general active project search if keyword search yielded no projects
        if (empty($projects)) {
            try {
                $projects = $client->searchActiveProjects($searchFilters, limit: max(20, $remainingToday * 3));
            } catch (\Throwable $e) {
                $account->status = 'error';
                $account->last_error = $e->getMessage();
                $account->save();
                Log::warning('Freelancer project search failed', ['account_id' => $account->id, 'error' => $e->getMessage()]);

                $summary['search_failed'] = true;
                $summary['error'] = $e->getMessage();

                return $summary;
            }
        }

        $summary['scanned'] = count($projects);
        $alreadyTracked = 0;

        foreach ($projects as $project) {
            if ($remainingToday <= 0) {
                break;
            }

            $projectId = (int) ($project['id'] ?? 0);
            if (! $projectId || FreelancerBid::where('freelancer_account_id', $account->id)->where('project_id', $projectId)->exists()) {
                $alreadyTracked++;
                continue;
            }

            [$eligible, $reason] = $this->eligibility->isEligible($project, $account);

            [$budgetMin, $budgetMax] = $this->eligibility->extractBudget($project);
            [$currencyCode, $currencySign] = $this->eligibility->extractCurrency($project);
            $country = $this->eligibility->extractCountry($project);

            if (! $eligible) {
                FreelancerBid::create([
                    'freelancer_account_id' => $account->id,
                    'project_id' => $projectId,
                    'project_title' => $project['title'] ?? null,
                    'project_url' => $this->projectUrl($account, $project),
                    'budget_min' => $budgetMin,
                    'budget_max' => $budgetMax,
                    'currency_code' => $currencyCode,
                    'currency_sign' => $currencySign,
                    'client_country' => $country,
                    'status' => 'skipped',
                    'error_message' => $reason,
                ]);
                $summary['skipped']++;

                continue;
            }

            $summary['eligible']++;

            $quote = $this->quotes->calculate($project, (float) $budgetMin, (float) $budgetMax, $account, (string) $currencyCode);
            $bidAmount = $quote['amount'];
            $bidPeriod = $quote['period_days'];
            $proposal = $this->proposals->generate($project, $account);

            $bid = FreelancerBid::create([
                'freelancer_account_id' => $account->id,
                'project_id' => $projectId,
                'project_title' => $project['title'] ?? null,
                'project_url' => $this->projectUrl($account, $project),
                'budget_min' => $budgetMin,
                'budget_max' => $budgetMax,
                'currency_code' => $currencyCode,
                'currency_sign' => $currencySign,
                'client_country' => $country,
                'bid_amount' => $bidAmount,
                'bid_period_days' => $bidPeriod,
                'internal_cost' => $quote['internal_cost'],
                'internal_timeline_days' => $quote['internal_timeline_days'],
                'proposal_text' => $proposal,
                'status' => 'pending',
            ]);

            if ($account->auto_submit_bids) {
                $this->submit($bid, $account, $client);
                if ($bid->status === 'submitted') {
                    $summary['submitted']++;
                } else {
                    $summary['errors']++;
                }
            } else {
                $summary['drafted']++;
            }

            $remainingToday--;
        }

        $summary['already_tracked'] = $alreadyTracked;

        $account->last_scanned_at = now();
        $account->save();

        return $summary;
    }

    public function submit(FreelancerBid $bid, ?FreelancerAccount $account = null, ?FreelancerApiClient $client = null): void
    {
        $account ??= $bid->account;
        $client ??= new FreelancerApiClient($account);

        try {
            $client->placeBid(
                $bid->project_id,
                (float) $bid->bid_amount,
                (int) $bid->bid_period_days,
                (string) $bid->proposal_text,
            );

            $bid->status = 'submitted';
            $bid->submitted_at = now();
            $bid->error_message = null;
        } catch (\Throwable $e) {
            $bid->status = 'failed';
            $bid->error_message = $e->getMessage();
        }

        $bid->save();
    }

    protected function projectUrl(FreelancerAccount $account, array $project): ?string
    {
        $seoUrl = $project['seo_url'] ?? null;

        return $seoUrl ? rtrim((string) $account->api_url, '/').'/projects/'.$seoUrl : null;
    }
}
