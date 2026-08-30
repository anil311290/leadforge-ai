<?php

namespace App\Services\Discovery;

use App\Models\Lead;

class DuplicateDetector
{
    /**
     * Detect an existing lead matching the candidate within a campaign.
     * Returns matched lead id + key + similarity, or null.
     */
    public function findDuplicate(array $candidate, $campaignId = null): ?array
    {
        $domain = DataNormalizer::normalizeDomain($candidate['website'] ?? null);
        $company = DataNormalizer::normalizeCompany($candidate['name'] ?? null);
        $phone = DataNormalizer::normalizePhone($candidate['phone'] ?? null);
        $email = isset($candidate['email']) ? strtolower(trim((string) $candidate['email'])) : null;

        $base = Lead::query();

        if ($campaignId) {
            $base->where('campaign_id', $campaignId);
        }

        if ($domain) {
            $exact = (clone $base)->where('normalized_domain', $domain)->first();
            if ($exact) {
                return ['lead_id' => $exact->id, 'matched_on' => 'domain', 'similarity' => 100.0];
            }
        }

        if ($phone) {
            $phoneMatch = (clone $base)->where('phone', $phone)->first();
            if ($phoneMatch) {
                return ['lead_id' => $phoneMatch->id, 'matched_on' => 'phone', 'similarity' => 100.0];
            }
        }

        if ($email) {
            $emailMatch = (clone $base)->where('email', $email)->first();
            if ($emailMatch) {
                return ['lead_id' => $emailMatch->id, 'matched_on' => 'email', 'similarity' => 100.0];
            }
        }

        if ($company) {
            $nameMatch = (clone $base)->where('normalized_company', $company)->first();
            if ($nameMatch) {
                return ['lead_id' => $nameMatch->id, 'matched_on' => 'company', 'similarity' => 95.0];
            }
        }

        return null;
    }
}