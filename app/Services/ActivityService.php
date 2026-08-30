<?php

namespace App\Services;

use App\Models\Activity;

class ActivityService
{
    public function log($user, string $type, string $entity = 'Lead', $entityId = null, ?string $title = null, array $payload = []): Activity
    {
        return Activity::create([
            'user_id' => $user?->id,
            'campaign_id' => $this->campaignIdFor($entity, $entityId),
            'lead_id' => $entity === 'Lead' ? $entityId : $payload['lead_id'] ?? null,
            'type' => $type,
            'title' => $title,
            'payload' => $payload,
        ]);
    }

    protected function campaignIdFor(string $entity, $entityId): ?int
    {
        if ($entity === 'Campaign') {
            return $entityId;
        }
        if ($entity === 'Lead' && $entityId) {
            return \App\Models\Lead::find($entityId)?->campaign_id;
        }

        return null;
    }
}