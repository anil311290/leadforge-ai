<?php

namespace App\Services\Discovery\Providers;

use App\Services\Discovery\Contracts\DiscoveryProvider;
use App\Services\Discovery\DataNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchApiDiscoveryProvider implements DiscoveryProvider
{
    public function name(): string
    {
        return 'search_api';
    }

    public function isConfigured(): bool
    {
        $config = config('leadforge.discovery.search_api');

        return ! empty($config['endpoint']) && ! empty($config['api_key']);
    }

    public function discover(string $location, array $options = []): array
    {
        $config = config('leadforge.discovery.search_api');
        $max = min((int) ($options['max'] ?? 20), 60);
        $query = $options['query'] ?: config('leadforge.discovery.search_api.default_query', 'shops without websites');
        $endpoint = $config['endpoint'];
        $apiKey = $config['api_key'];

        if (! $endpoint || ! $apiKey) {
            throw new \RuntimeException('Google Places API is not configured. Set LF_DISCOVERY_API_KEY or GOOGLE_MAPS_API_KEY in .env.');
        }

        $searches = $this->buildSearchQueries($location, $query);
        $results = [];
        $seen = [];

        foreach ($searches as $textQuery) {
            if (count($results) >= $max) {
                break;
            }

            Log::info("[GooglePlaces] Searching {$textQuery}");

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.primaryType,places.types,places.businessStatus',
                ])
                ->post($endpoint, [
                    'textQuery' => $textQuery,
                    'pageSize' => min(20, max(1, $max - count($results))),
                    'regionCode' => 'IN',
                    'languageCode' => 'en',
                ]);

            if (! $response->successful()) {
                Log::warning('[GooglePlaces] Request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                continue;
            }

            foreach ($response->json('places', []) as $place) {
                if (count($results) >= $max) {
                    break 2;
                }

                if (! empty($place['websiteUri']) || ($place['businessStatus'] ?? 'OPERATIONAL') !== 'OPERATIONAL') {
                    continue;
                }

                $name = $place['displayName']['text'] ?? null;
                if (! $name || $this->looksLikeItCompany($name, $place)) {
                    continue;
                }

                $key = $place['id'] ?? DataNormalizer::normalizeCompany($name).'|'.($place['formattedAddress'] ?? '');
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $phone = DataNormalizer::normalizeIndianMobile($place['nationalPhoneNumber'] ?? $place['internationalPhoneNumber'] ?? null);

                $results[] = [
                    'name' => $name,
                    'website' => null,
                    'industry' => $this->humanizeType($place['primaryType'] ?? ($place['types'][0] ?? null)),
                    'location' => $place['formattedAddress'] ?? $location,
                    'city' => $location,
                    'phone' => $phone,
                    'email' => null,
                    'address' => $place['formattedAddress'] ?? null,
                    'description' => 'Google-registered local business without a listed website.',
                    'why_need_software' => 'No website is listed on Google, so this business may need a website, online catalogue, booking, e-commerce, CRM or follow-up system.',
                ];
            }
        }

        Log::info('[GooglePlaces] Imported '.count($results).' businesses without websites');

        return $results;
    }

    protected function buildSearchQueries(string $location, string $query): array
    {
        return array_unique([
            "shops in {$location} without website",
            "retail stores in {$location} without website",
            "grocery stores in {$location} without website",
            "clothing stores in {$location} without website",
            "restaurants and cafes in {$location} without website",
            "clinics in {$location} without website",
            "salons and gyms in {$location} without website",
            "local businesses in {$location} without website",
            "{$query} in {$location}",
        ]);
    }

    protected function looksLikeItCompany(string $name, array $place): bool
    {
        $text = strtolower(implode(' ', array_filter([
            $name,
            $place['primaryType'] ?? null,
            implode(' ', $place['types'] ?? []),
        ])));

        foreach (['software', 'web development', 'app development', 'digital marketing', 'seo', 'it services', 'technology solutions', 'computer training'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function humanizeType(?string $type): ?string
    {
        if (! $type) {
            return null;
        }

        return ucwords(str_replace('_', ' ', $type));
    }
}
