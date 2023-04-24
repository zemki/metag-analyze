<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterAction
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $action = [];
        $action['user_id'] = (Auth::user() ? Auth::user()->id : null);
        $action['url'] = $request->fullUrl();
// Get the currently authenticated user...
        DB::table('actions')->insert([$action]);

        return $next($request);
    }
}
