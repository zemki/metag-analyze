<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->groups()->count() >= 1) {
            return $next($request);
        }

        return redirect(route('new_group'))->with('message', 'You must belong to a group to use Metag analyze.  If you think this is an error, contact the system admin.');
    }
}
