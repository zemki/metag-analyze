<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockIpMiddleware
{
    /**
     * Get the list of blocked IP addresses.
     *
     * Reads config, not env(): once `php artisan config:cache` has run the
     * .env file is no longer loaded, so an env() call here would silently
     * return null and disable IP blocking altogether.
     *
     * config('utilities.blockedIps') explodes BLOCKED_IPS on commas, which
     * yields [''] when the variable is empty - hence the filter below.
     *
     * @return array
     */
    protected function getBlockedIps(): array
    {
        return array_values(array_filter(
            array_map('trim', (array) config('utilities.blockedIps', [])),
            fn ($ip) => $ip !== ''
        ));
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
