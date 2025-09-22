<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockIpMiddleware
{
    public array $blockIps = ['45.93.9.139', '193.168.141.21', '45.86.86.223'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (in_array($request->ip(), $this->blockIps)) {
            abort(403, 'Nope.');
        }

        return $next($request);
    }
}
