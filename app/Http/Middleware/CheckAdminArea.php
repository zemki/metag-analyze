<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdminArea
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'You are not authorized');
        }

        return $next($request);
    }
}
