<?php

namespace App\Services\Discovery;

use App\Jobs\ScanWebsite;
use App\Jobs\AnalyseLead;
use App\Models\Campaign;
use App\Models\CampaignSource;
use App\Models\Lead;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Log;

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
            Log::info("[Campaign {$campaign->id}] Discovery started");
            $this->activities->log($campaign->user, 'campaign_running', 'Campaign', $campaign->id, 'Discovery started');
            $this->setProgress($campaign, 5, 'Starting discovery…');

            [$leadsCreated, $duplicatesSkipped] = $this->discover($campaign);

            Log::info("[Campaign {$campaign->id}] Discovery complete — ".count($leadsCreated)." leads, {$duplicatesSkipped} duplicates");
            $this->setProgress($campaign, 60, 'Discovery complete — '.count($leadsCreated).' leads found. Scanning websites…');

            foreach ($leadsCreated as $lead) {
                if ($lead->normalized_domain) {
                    Log::info("[Campaign {$campaign->id}] Queuing website scan for {$lead->company} ({$lead->normalized_domain})");
                    dispatch(new ScanWebsite($lead));
                }
            }

            $this->setProgress($campaign, 80, 'Website scans queued. Analysing opportunities…');

            $campaign->update([
                'status' => 'completed',
                'progress' => 100,
                'progress_message' => 'Campaign completed — '.count($leadsCreated).' leads discovered.',
                'completed_at' => now(),
            ]);

            Log::info("[Campaign {$campaign->id}] Campaign completed");
            $this->activities->log(
                $campaign->user,
                'campaign_completed',
                'Campaign',
                $campaign->id,
                sprintf('Discovered %d leads (%d duplicates skipped)', count($leadsCreated), $duplicatesSkipped)
            );
        } catch (\Throwable $e) {
            Log::error("[Campaign {$campaign->id}] Discovery failed: ".$e->getMessage());
            $campaign->update(['status' => 'failed', 'error' => $e->getMessage(), 'progress_message' => 'Failed: '.$e->getMessage()]);
            $this->activities->log($campaign->user, 'campaign_failed', 'Campaign', $campaign->id, 'Discovery failed: '.$e->getMessage());
        }
    }

    protected function setProgress(Campaign $campaign, int $percent, string $message): void
    {
        Log::info("[Campaign {$campaign->id}] {$percent}% — {$message}");
        $campaign->update([
            'progress' => $percent,
            'progress_message' => $message,
        ]);
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

        $enabledProviders = array_filter($providers, fn ($p) => $p->isConfigured());
        $totalProviders = max(count($enabledProviders), 1);

        foreach ($enabledProviders as $i => $provider) {
            $base = 10; // provider progress starts at 10%
            $span = 45; // providers span 10% -> 55%
            $providerBase = $base + (int) round(($i / $totalProviders) * $span);
            $providerEnd = $base + (int) round((($i + 1) / $totalProviders) * $span);

            $label = str_replace('_', ' ', ucwords($provider->name()));

            $this->setProgress($campaign, $providerBase, "Running provider «{$label}»…");

            $source = CampaignSource::create([
                'campaign_id' => $campaign->id,
                'provider' => $provider->name(),
                'status' => 'running',
            ]);

            try {
                $found = $provider->discover($campaign->location, [
                    'max' => $limit ?? 50,
                    'query' => $campaign->parameters['query'] ?? null,
                    'path' => $campaign->parameters['path'] ?? null,
                    'businesses' => $campaign->parameters['businesses'] ?? [],
                ]);

                $source->update(['items_found' => count($found)]);
                $this->setProgress($campaign, $providerBase + 5, "{$label}: found ".count($found).' businesses — checking for duplicates…');

                $total = max(count($found), 1);
                foreach ($found as $idx => $candidate) {
                    if ($limit && count($leadsCreated) >= $limit) {
                        break 2;
                    }

                    $progress = $providerBase + 5 + (int) round((($idx + 1) / $total) * ($providerEnd - $providerBase - 5));
                    $this->setProgress($campaign, $progress, "{$label}: processing ".(int) ($idx + 1).' of '.count($found));

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