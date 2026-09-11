<?php

namespace App\Services\Freelancer;

use App\Exceptions\FreelancerApiException;
use App\Models\FreelancerAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Freelancer.com REST API (api.freelancer.com / www.freelancer.com)
 * scoped to a single account's OAuth token, so each account bids independently.
 */
class FreelancerApiClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct(protected FreelancerAccount $account)
    {
        $this->baseUrl = rtrim((string) ($account->api_url ?: config('freelancer.api_url')), '/');
        $this->token = (string) $account->oauth_token;
    }

    protected function request()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['freelancer-oauth-v1' => $this->token])
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Verify the token is valid by fetching the authenticated user's profile.
     */
    public function getSelfUser(): array
    {
        $response = $this->request()->get('/api/users/0.1/self/', ['compact' => 'true']);

        if ($response->failed()) {
            $this->logFailure($response, 'auth check');
            throw FreelancerApiException::fromResponse($response, 'verify this account');
        }

        return (array) $response->json('result', []);
    }

    /**
     * Search active projects matching a free-text query.
     */
    public function searchActiveProjects(array $filters = [], int $limit = 20): array
    {
        $query = array_merge([
            'limit' => $limit,
            'offset' => 0,
            'compact' => 'true',
            'full_description' => 'true',
            'job_details' => 'true',
            'user_details' => 'true',
            'budget_details' => 'true',
        ], $filters);

        $response = $this->request()->get('/api/projects/0.1/projects/active/', $query);

        if ($response->failed()) {
            $this->logFailure($response, 'project search');
            throw FreelancerApiException::fromResponse($response, 'search projects');
        }

        $projects = (array) $response->json('result.projects', []);

        // Freelancer's API returns titles/descriptions HTML-entity encoded (e.g. "&amp;");
        // decode once here so nothing downstream double-escapes or shows raw entities.
        return array_map(function (array $project) {
            foreach (['title', 'description', 'preview_description'] as $field) {
                if (isset($project[$field]) && is_string($project[$field])) {
                    $project[$field] = html_entity_decode($project[$field], ENT_QUOTES | ENT_HTML5);
                }
            }

            return $project;
        }, $projects);
    }

    /**
     * Submit a bid on a project. Returns the API result payload.
     */
    public function placeBid(int $projectId, float $amount, int $periodDays, string $description): array
    {
        $bidderId = (int) ($this->getSelfUser()['id'] ?? 0);

        $response = $this->request()->post('/api/projects/0.1/bids/', [
            'project_id' => $projectId,
            'bidder_id' => $bidderId,
            'amount' => $amount,
            'period' => $periodDays,
            'milestone_percentage' => 100,
            'description' => $description,
        ]);

        if ($response->failed()) {
            $this->logFailure($response, 'bid submission');
            throw FreelancerApiException::fromResponse($response, 'place this bid');
        }

        return (array) $response->json('result', []);
    }

    protected function logFailure($response, string $action): void
    {
        Log::warning("Freelancer API {$action} failed", [
            'account_id' => $this->account->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
}
