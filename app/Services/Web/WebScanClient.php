<?php

namespace App\Services\Web;

use Illuminate\Support\Facades\Http;

class WebScanClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.worker.url', 'http://127.0.0.1:8001');
    }

    public function isConfigured(): bool
    {
        return (bool) $this->baseUrl;
    }

    public function isAvailable(): bool
    {
        try {
            $r = Http::timeout(3)->get(rtrim($this->baseUrl, '/').'/health');

            return $r->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Crawl a website and return structured scan data.
     */
    public function crawl(string $url): array
    {
        $response = Http::timeout(120)
            ->post(rtrim($this->baseUrl, '/').'/scan', [
                'url' => $url,
                'max_pages' => config('leadforge.crawler.max_pages', 10),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Crawl worker responded '.$response->status().': '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Analyse a crawled site. Provided for completeness; the Python worker
     * exposes /analyse so the same provider+prompt pipeline is reusable.
     */
    public function analyse(array $payload): array
    {
        $response = Http::timeout(120)
            ->post(rtrim($this->baseUrl, '/').'/analyse', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Analysis worker responded '.$response->status());
        }

        return $response->json() ?? [];
    }
}