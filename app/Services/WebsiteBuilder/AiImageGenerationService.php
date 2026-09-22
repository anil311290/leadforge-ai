<?php

namespace App\Services\WebsiteBuilder;

use App\Models\BusinessWebsite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AiImageGenerationService
{
    public function generate(string $prompt, string $directory): string
    {
        $apiKey = config('leadforge.ai.api_key');
        if (! $apiKey) {
            throw new RuntimeException('AI image generation is not configured. Set LF_AI_API_KEY first.');
        }

        $provider = config('leadforge.ai.provider', 'openai');
        $baseUrl = rtrim((string) config('leadforge.ai.base_url', ''), '/') ?: ($provider === 'openrouter'
            ? 'https://openrouter.ai/api/v1'
            : 'https://api.openai.com/v1');

        $payload = [
            'model' => config('leadforge.ai.image_model', 'gpt-image-1'),
            'prompt' => $prompt,
            'size' => '1024x1024',
            'n' => 1,
        ];

        $response = Http::timeout((int) config('leadforge.ai.timeout', 120))
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/images/generations', $payload);

        if ($response->failed()) {
            $providerMessage = (string) $response->json('error.message', '');
            if ($response->status() === 429) {
                throw new RuntimeException('OpenAI image generation is unavailable because the API quota or billing limit was reached. Check your OpenAI account usage and billing, then try again.');
            }

            throw new RuntimeException('AI image provider error '.$response->status().($providerMessage ? ': '.$providerMessage : '.'));
        }

        $encoded = $response->json('data.0.b64_json');
        if (! is_string($encoded) || $encoded === '') {
            throw new RuntimeException('AI image provider returned no image data.');
        }

        $path = trim($directory, '/').'/'.uniqid('ai-', true).'.png';
        Storage::disk('public')->put($path, base64_decode($encoded, true));

        return $path;
    }

    public function businessPrompt(BusinessWebsite $website, string $assetType, string $subject = ''): string
    {
        $business = $website->business_name;
        $location = trim(implode(', ', array_filter([$website->city, $website->state])));
        $context = $location ? " serving customers in {$location}" : '';
        $subject = $subject ? " about {$subject}" : '';

        return match ($assetType) {
            'banner' => "Create a polished, realistic website hero banner for {$business}{$context}. Show a welcoming local business environment, no text, no logo, no watermark, professional natural lighting, wide composition.",
            'product' => "Create a clean commercial product-category image for {$business}{$subject}. Show relevant products arranged professionally, no readable text, no brand logos, no watermark, suitable for a website card.",
            'service' => "Create a warm professional image representing {$subject} offered by {$business}{$context}. No readable text, no logos, no watermark, suitable for a website service card.",
            default => "Create a professional website image for {$business}. No readable text, no logos, no watermark.",
        };
    }
}
