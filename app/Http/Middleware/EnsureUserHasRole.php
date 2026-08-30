<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($role === 'admin' && $user->role !== 'admin') {
            abort(403, 'Administrator access required.');
        }

        return $next($request);
    }
}