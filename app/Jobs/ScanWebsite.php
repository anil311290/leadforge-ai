<?php

namespace App\Jobs;

use App\Services\ActivityService;
use App\Models\Lead;
use App\Models\WebsitePage;
use App\Models\WebsiteScan;
use App\Models\WebsiteScanSignal;
use App\Models\WebsiteTechnology;
use App\Services\Web\WebScanClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ScanWebsite implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $timeout = 180;
    public $backoff = [5, 20];

    private static ?bool $shouldQueueCheck = null;

    public function __construct(public Lead $lead)
    {
    }

    public static function shouldQueue(): bool
    {
        if (self::$shouldQueueCheck === null) {
            self::$shouldQueueCheck = config('queue.default') !== 'sync' && (bool) app(WebScanClient::class)->isConfigured();
        }

        return self::$shouldQueueCheck;
    }

    public function handle(WebScanClient $client, ActivityService $activities): void
    {
        if (! $this->lead->normalized_domain) {
            return;
        }

        // Re-scan prevention: skip if already scanned successfully
        $existingScan = WebsiteScan::where('lead_id', $this->lead->id)
            ->where('status', 'completed')
            ->first();

        if ($existingScan) {
            Log::info("[Scan] Skipping {$this->lead->company} — already scanned successfully");
            $this->lead->update(['status' => Lead::STATUS_ANALYZED]);

            if ($this->lead->campaign?->auto_analysis_enabled) {
                dispatch(new AnalyseLead($this->lead));
            }

            return;
        }

        $scan = WebsiteScan::firstOrNew(['lead_id' => $this->lead->id]);
        $scan->lead_id = $this->lead->id;
        $scan->url = $this->lead->website ?: $this->lead->normalized_domain;
        $scan->domain = $this->lead->normalized_domain;
        $scan->status = 'scanning';
        $scan->save();

        try {
            if (! $client->isAvailable()) {
                $scan->update(['status' => 'failed', 'error' => 'Crawl worker is not available.', 'scanned_at' => now()]);
                $this->lead->update(['status' => Lead::STATUS_ANALYZED, 'analysis' => null]);

                return;
            }

            $data = $client->crawl($scan->url);

            $scan->forceFill([
                'http_status' => $data['http_status'] ?? null,
                'https_enabled' => $data['https_enabled'] ?? null,
                'title' => $data['title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'robots_txt' => $data['robots_txt'] ?? null,
                'sitemap' => $data['sitemap'] ?? null,
                'cms' => $data['cms'] ?? null,
                'ecommerce_platform' => $data['ecommerce_platform'] ?? null,
                'page_count' => count($data['pages'] ?? []),
                'data_quality' => $data['data_quality'] ?? 0,
                'statistics' => $data['statistics'] ?? null,
                'business_data' => $data['business_data'] ?? null,
                'status' => 'completed',
                'scanned_at' => now(),
            ])->save();

            // Store pages
            WebsitePage::where('scan_id', $scan->id)->delete();
            foreach (($data['pages'] ?? []) as $page) {
                WebsitePage::create([
                    'scan_id' => $scan->id,
                    'url' => $page['url'] ?? '',
                    'path' => $page['path'] ?? null,
                    'http_status' => $page['http_status'] ?? null,
                    'title' => $page['title'] ?? null,
                    'meta_description' => $page['meta_description'] ?? null,
                    'text_content' => isset($page['text_content']) ? substr($page['text_content'], 0, 100000) : null,
                    'page_type' => $page['page_type'] ?? null,
                    'page_size_kb' => $page['page_size_kb'] ?? null,
                ]);
            }

            // Store technologies
            WebsiteTechnology::where('scan_id', $scan->id)->delete();
            foreach (($data['technologies'] ?? []) as $tech) {
                WebsiteTechnology::create([
                    'scan_id' => $scan->id,
                    'name' => $tech['name'] ?? 'unknown',
                    'category' => $tech['category'] ?? 'frontend',
                    'version' => $tech['version'] ?? null,
                    'confidence' => $tech['confidence'] ?? null,
                ]);
            }

            // Store signals
            WebsiteScanSignal::where('scan_id', $scan->id)->delete();
            foreach (($data['signals'] ?? []) as $signal) {
                WebsiteScanSignal::create([
                    'scan_id' => $scan->id,
                    'signal' => $signal['signal'] ?? '',
                    'signal_type' => $signal['signal_type'] ?? 'evidence',
                    'category' => $signal['category'] ?? 'business',
                    'confidence' => $signal['confidence'] ?? null,
                    'detail' => $signal['detail'] ?? null,
                ]);
            }

            // Extract contact details from business_data and update lead
            $this->extractContacts($this->lead, $data['business_data'] ?? []);

            $this->lead->update(['status' => Lead::STATUS_ANALYZED]);
            $activities->log($this->lead->owner, 'analysis_completed', 'Lead', $this->lead->id, 'Website analysed for '.$this->lead->company);

            $this->updateScanProgress($this->lead);

            if ($this->lead->campaign?->auto_analysis_enabled) {
                dispatch(new AnalyseLead($this->lead));
            }
        } catch (\Throwable $e) {
            Log::error('Website scan failed', ['lead' => $this->lead->id, 'error' => $e->getMessage()]);
            $scan->update(['status' => 'failed', 'error' => $e->getMessage(), 'scanned_at' => now()]);
            $this->lead->update(['status' => Lead::STATUS_ANALYZED, 'analysis' => ['error' => $e->getMessage()]]);
        }
    }

    /**
     * Update campaign progress based on how many leads have been scanned.
     */
    protected function updateScanProgress($lead): void
    {
        $campaign = $lead->campaign;
        if (! $campaign || ! in_array($campaign->status, ['running', 'paused'])) {
            return;
        }

        $total = max($campaign->leads()->count(), 1);
        $scanned = $campaign->leads()->whereHas('scans', fn ($q) => $q->where('status', 'completed'))->count();
        $analysed = $campaign->leads()->whereNotNull('analyzed_at')->count();

        // Scanning phase: 55% → 85%, Analysis phase: 85% → 100%
        if ($campaign->auto_analysis_enabled) {
            $percent = 55 + (int) round((($scanned / $total) * 30) + (($analysed / $total) * 15));
        } else {
            $percent = 55 + (int) round(($scanned / $total) * 45);
        }

        \App\Models\Campaign::where('id', $campaign->id)->update([
            'progress' => min($percent, 99),
            'progress_message' => "Scanning & analysing: {$scanned}/{$total} websites processed",
        ]);
    }

    /**
     * Extract contact details from crawled business_data and update the lead.
     */
    protected function extractContacts($lead, array $businessData): void
    {
        $updates = [];

        // Extract phone
        if (! $lead->phone && ! empty($businessData['phone'])) {
            $updates['phone'] = $businessData['phone'];
        }

        // Extract email
        if (! $lead->email && ! empty($businessData['email'])) {
            $updates['email'] = $businessData['email'];
        }

        // Extract address
        if (! $lead->address && ! empty($businessData['address'])) {
            $updates['address'] = $businessData['address'];
        }

        if (! empty($updates)) {
            $lead->update($updates);
            Log::info("[Scan] Extracted contacts for {$lead->company}: ".json_encode($updates));
        }

        // Extract people/contacts from business_data
        $people = $businessData['contacts'] ?? $businessData['people'] ?? [];
        if (! empty($people) && is_array($people)) {
            foreach ($people as $person) {
                if (is_string($person)) {
                    continue;
                }
                $existing = \App\Models\LeadContact::where('lead_id', $lead->id)
                    ->where(function ($q) use ($person) {
                        if (! empty($person['email'])) {
                            $q->where('email', $person['email']);
                        } elseif (! empty($person['name'])) {
                            $q->where('name', $person['name']);
                        }
                    })->exists();

                if (! $existing) {
                    \App\Models\LeadContact::create([
                        'lead_id' => $lead->id,
                        'name' => $person['name'] ?? null,
                        'role' => $person['role'] ?? $person['title'] ?? null,
                        'email' => $person['email'] ?? null,
                        'phone' => $person['phone'] ?? null,
                        'is_primary' => ! \App\Models\LeadContact::where('lead_id', $lead->id)->exists(),
                    ]);
                }
            }
        }
    }
}