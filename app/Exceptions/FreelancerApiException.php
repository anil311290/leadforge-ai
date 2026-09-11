<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

/**
 * Thrown for any Freelancer.com API failure, carrying a short, user-friendly
 * message suitable for display in the UI (the raw API response is logged separately).
 */
class FreelancerApiException extends RuntimeException
{
    public static function fromResponse(Response $response, string $action): self
    {
        $status = $response->status();
        $body = (array) $response->json();
        $apiMessage = (string) ($body['message'] ?? $body['error']['message'] ?? '');

        $friendly = match (true) {
            $status === 401 || $status === 403 => 'This Freelancer.com account key is invalid or expired. Please update the OAuth token in Freelancer Accounts.',
            $status === 429 => 'Freelancer.com is rate-limiting this account right now. It will retry automatically on the next scan.',
            str_contains(mb_strtolower($apiMessage), 'already placed a bid') => 'A bid was already placed on this project from this account.',
            str_contains(mb_strtolower($apiMessage), 'insufficient') => "This account doesn't have enough bid tokens left on Freelancer.com.",
            str_contains(mb_strtolower($apiMessage), 'closed') || str_contains(mb_strtolower($apiMessage), 'expired') => 'This project is no longer accepting bids.',
            $status >= 500 => 'Freelancer.com is temporarily unavailable. It will retry automatically on the next scan.',
            $apiMessage !== '' => $apiMessage,
            default => "Could not {$action} on Freelancer.com. Please try again later.",
        };

        return new self($friendly, $status);
    }
}
