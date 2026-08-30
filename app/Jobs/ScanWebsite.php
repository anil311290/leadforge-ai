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

            $this->lead->update(['status' => Lead::STATUS_ANALYZED]);
            $activities->log($this->lead->owner, 'analysis_completed', 'Lead', $this->lead->id, 'Website analysed for '.$this->lead->company);

            if ($this->lead->campaign?->auto_analysis_enabled) {
                dispatch(new AnalyseLead($this->lead));
            }
        } catch (\Throwable $e) {
            Log::error('Website scan failed', ['lead' => $this->lead->id, 'error' => $e->getMessage()]);
            $scan->update(['status' => 'failed', 'error' => $e->getMessage(), 'scanned_at' => now()]);
            $this->lead->update(['status' => Lead::STATUS_ANALYZED, 'analysis' => ['error' => $e->getMessage()]]);
        }
    }
}