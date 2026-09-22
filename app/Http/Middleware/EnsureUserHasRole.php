<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values()
            ->all();

        if ($allowedRoles === []) {
            return $next($request);
        }

        $isAllowed = in_array($user->role, $allowedRoles, true)
            || ($user->role === 'admin' && in_array('admin', $allowedRoles, true));

        if (! $isAllowed) {
            abort(403, 'You do not have access to this section.');
        }

        return $next($request);
    }
}