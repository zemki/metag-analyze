<?php

namespace App\Http\Middleware;

use Closure;

class Researcher
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

    
    if ( Auth::check() && Auth::user()->isResearcher() )
     {
        return $next($request);
    }


    return $next($request);
    }
}
