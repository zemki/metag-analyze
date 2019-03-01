<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Action;
use Closure;
use Illuminate\Support\Facades\DB;

class RegisterAction
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
        $action = [];
        $action['user_id'] = (Auth::user() ? Auth::user()->id : null );
        $action['url'] = $request->fullUrl();
// Get the currently authenticated user...
        $user = Auth::user();
        DB::table('actions')->insert([$action]);
        return $next($request);
    }
}
