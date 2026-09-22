<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RejectWebsiteBuilderUser
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->isWebsiteBuilder()) {
            abort(403, 'Website Builder users cannot access the main application.');
        }

        return $next($request);
    }
}
