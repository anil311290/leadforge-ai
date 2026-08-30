<?php

namespace App\Services\Discovery;

use App\Jobs\ScanWebsite;
use App\Jobs\AnalyseLead;
use App\Models\Campaign;
use App\Models\CampaignSource;
use App\Models\Lead;
use App\Services\ActivityService;

class DiscoveryEngine
{
    public function __construct(
        protected DiscoveryManager $manager,
        protected DuplicateDetector $duplicates,
        protected ActivityService $activities
    ) {
    }

    public static function start(Campaign $campaign): void
    {
        dispatch(new \App\Jobs\RunCampaign($campaign));
    }

    /**
     * Run the full discovery pipeline for a campaign synchronously.
     */
    public function runForCampaign(Campaign $campaign): void
    {
        try {
            $this->activities->log($campaign->user, 'campaign_running', 'Campaign', $campaign->id, 'Discovery started');

            [$leadsCreated, $duplicatesSkipped] = $this->discover($campaign);

            foreach ($leadsCreated as $lead) {
                if ($lead->normalized_domain) {
                    dispatch(new ScanWebsite($lead));
                }
            }

            $campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->activities->log(
                $campaign->user,
                'campaign_completed',
                'Campaign',
                $campaign->id,
                sprintf('Discovered %d leads (%d duplicates skipped)', count($leadsCreated), $duplicatesSkipped)
            );
        } catch (\Throwable $e) {
            $campaign->update(['status' => 'failed', 'error' => $e->getMessage()]);
            $this->activities->log($campaign->user, 'campaign_failed', 'Campaign', $campaign->id, 'Discovery failed: '.$e->getMessage());
        }
    }

    /**
     * @return array{0: Lead[], 1: int} [created leads, duplicates skipped]
     */
    protected function discover(Campaign $campaign): array
    {
        $providers = $this->manager->getProviders();
        if (empty($providers)) {
            throw new \RuntimeException('No discovery providers configured.');
        }

        $leadsCreated = [];
        $duplicates = 0;
        $limit = $campaign->max_businesses;

        foreach ($providers as $provider) {
            if (! $provider->isConfigured()) {
                continue;
            }

            $source = CampaignSource::create([
                'campaign_id' => $campaign->id,
                'provider' => $provider->name(),
                'status' => 'running',
            ]);

            try {
                $found = $provider->discover($campaign->location, [
                    'max' => $limit ?? 50,
                    'query' => $campaign->parameters['query'] ?? null,
                ]);

                $source->update(['items_found' => count($found)]);

                foreach ($found as $candidate) {
                    if ($limit && count($leadsCreated) >= $limit) {
                        break 2;
                    }

                    $match = $this->duplicates->findDuplicate($candidate, $campaign->id);

                    if ($match) {
                        \App\Models\LeadDuplicate::create([
                            'lead_id' => $match['lead_id'],
                            'matched_on' => $match['matched_on'],
                            'similarity' => $match['similarity'],
                            'status' => 'linked',
                        ]);
                        $duplicates++;
                        continue;
                    }

                    $lead = $this->createLead($campaign, $candidate, $provider->name());
                    $leadsCreated[] = $lead;
                }

                $source->update(['items_imported' => count($leadsCreated), 'status' => 'completed']);
            } catch (\Throwable $e) {
                $source->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }
        }

        return [$leadsCreated, $duplicates];
    }

    protected function createLead(Campaign $campaign, array $candidate, string $provider): Lead
    {
        $website = $candidate['website'] ?? null;
        $domain = DataNormalizer::normalizeDomain($website);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'owner_id' => $campaign->user_id,
            'company' => $candidate['name'],
            'normalized_company' => DataNormalizer::normalizeCompany($candidate['name']),
            'website' => $website,
            'normalized_domain' => $domain,
            'industry' => $candidate['industry'] ?? null,
            'location' => $candidate['location'] ?? $campaign->location,
            'city' => $candidate['city'] ?? $campaign->location,
            'phone' => $candidate['phone'] ?? null,
            'email' => $candidate['email'] ?? null,
            'address' => $candidate['address'] ?? null,
            'source' => $provider,
            'status' => Lead::STATUS_DISCOVERED,
        ]);

        if (($candidate['email'] ?? null) || ($candidate['phone'] ?? null)) {
            \App\Models\LeadContact::create([
                'lead_id' => $lead->id,
                'email' => $candidate['email'] ?? null,
                'phone' => $candidate['phone'] ?? null,
                'is_primary' => true,
            ]);
        }

        $this->activities->log($campaign->user, 'lead_discovered', 'Lead', $lead->id, 'Discovered '.$lead->company);

        return $lead;
    }
}