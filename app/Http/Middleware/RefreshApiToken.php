<?php

namespace App\Http\Middleware;

use Closure;
use Helper;

class RefreshApiToken
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // Perform action
        $token = Helper::random_str(60);
        auth()->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        $response->token = $token;

        return $response;
    }
}
