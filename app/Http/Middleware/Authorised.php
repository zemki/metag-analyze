<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authorised
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
                Auth::check() && (Auth::user()->isAdmin()
                ||
                Auth::check() && Auth::user()->isResearcher())
        )
        {
            return $next($request);
        }

        return abort(401);    }
}
