<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Force the request to accept JSON responses.
     *
     * This ensures API routes always return JSON instead of
     * redirecting to HTML pages (e.g., login page on 401).
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
