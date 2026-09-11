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

            return $summary;
        }

        $remainingToday = max(0, $account->max_bids_per_day - $account->todayBidCount());

        if ($remainingToday <= 0) {
            $account->last_scanned_at = now();
            $account->save();

            return $summary;
        }

        try {
            $projects = $client->searchActiveProjects(limit: max(20, $remainingToday * 3));
        } catch (\Throwable $e) {
            $account->status = 'error';
            $account->last_error = $e->getMessage();
            $account->save();
            Log::warning('Freelancer project search failed', ['account_id' => $account->id, 'error' => $e->getMessage()]);

            return $summary;
        }

        $summary['scanned'] = count($projects);

        foreach ($projects as $project) {
            if ($remainingToday <= 0) {
                break;
            }

            $projectId = (int) ($project['id'] ?? 0);
            if (! $projectId || FreelancerBid::where('freelancer_account_id', $account->id)->where('project_id', $projectId)->exists()) {
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

            // Never bid below the project's own minimum, regardless of the account's default amount.
            $bidAmount = max($account->bid_amount_default > 0 ? (float) $account->bid_amount_default : 10, $budgetMin);
            $bidPeriod = $account->bid_period_days_default ?: 5;
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
