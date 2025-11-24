<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockIpMiddleware
{
    /**
     * Get the list of blocked IP addresses from environment configuration.
     *
     * @return array
     */
    protected function getBlockedIps(): array
    {
        $blockedIps = env('BLOCKED_IPS', '');

        if (empty($blockedIps)) {
            return [];
        }

        // Parse comma-separated IPs and trim whitespace
        return array_filter(
            array_map('trim', explode(',', $blockedIps)),
            fn($ip) => !empty($ip)
        );
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $blockedIps = $this->getBlockedIps();

        if (!empty($blockedIps) && in_array($request->ip(), $blockedIps)) {
            abort(403, 'Nope.');
        }

        return $next($request);
    }
}
