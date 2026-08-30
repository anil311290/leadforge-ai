<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight OpenAI-compatible chat client. Supports OpenAI, OpenRouter, and
 * Ollama via the LF_AI_PROVIDER / LF_AI_BASE_URL configuration.
 */
class AiClient
{
    protected string $provider;
    protected string $model;
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->provider = config('leadforge.ai.provider', 'openai');
        $this->model = config('leadforge.ai.model', 'gpt-4o-mini');
        $this->apiKey = config('leadforge.ai.api_key');
        $this->baseUrl = rtrim((string) config('leadforge.ai.base_url', ''), '/');
    }

    public function isConfigured(): bool
    {
        return (bool) $this->apiKey;
    }

    /**
     * Send a chat completion request. Returns the assistant message text.
     */
    public function complete(string $system, string $prompt, array $options = []): string
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('AI provider is not configured. Set LF_AI_API_KEY in .env.');
        }

        $base = $this->baseUrl ?: match ($this->provider) {
            'openrouter' => 'https://openrouter.ai/api/v1',
            default => 'https://api.openai.com/v1',
        };

        $endpoint = in_array($this->provider, ['ollama']) && ! $this->baseUrl
            ? $base.'/api/chat'
            : $base.'/chat/completions';

        $timeout = (int) config('leadforge.ai.timeout', 60);

        $request = Http::timeout($timeout)
            ->acceptJson();

        if ($this->provider !== 'ollama' && $this->apiKey) {
            $request->withToken($this->apiKey);
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an exact, structured business analysis engine.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => (float) ($options['temperature'] ?? config('leadforge.ai.temperature', 0.4)),
            'max_tokens' => (int) ($options['max_tokens'] ?? config('leadforge.ai.max_tokens', 2000)),
            'response_format' => ['type' => 'json_object'],
        ];

        $response = $request->post($endpoint, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('AI API error '.$response->status().': '.$response->body());
        }

        $content = data_get($response->json('choices.0.message.content'), 0, null);

        if (is_array($content)) {
            $content = $content['content'] ?? json_encode($content);
        }

        return (string) $content;
    }
}