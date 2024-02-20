<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockIpMiddleware
{
    public array $blockIps = ['45.93.9.139', '193.168.141.21'];

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (in_array($request->ip(), $this->blockIps)) {
            abort(403, 'Nope.');
        }

        return $next($request);
    }
}
