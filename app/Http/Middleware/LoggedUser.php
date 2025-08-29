<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class LoggedUser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            Auth::user()->latest_activity = Carbon::now()->toDateTimeString();
            Auth::user()->save();
        }

        return $next($request);
    }
}
