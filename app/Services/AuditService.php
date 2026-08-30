<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function record($user, string $action, string $entity, $entityId = null, ?array $before = null, ?array $after = null): void
    {
        // Never log sensitive data
        if (is_array($before)) {
            unset($before['password'], $before['oauth_tokens'], $before['api_key'], $before['token'], $before['api_secret']);
        }
        if (is_array($after)) {
            unset($after['password'], $after['oauth_tokens'], $after['api_key'], $after['token'], $after['api_secret']);
        }

        AuditLog::create([
            'user_id' => $user?->id ?? Auth::id(),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
        ]);
    }
}