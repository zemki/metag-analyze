<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Basic Auth guard for the API docs (/docs/api).
 *
 * Credentials come from config/scramble.php (API_DOCS_USER / API_DOCS_PASSWORD).
 * When no password is configured, the route fails closed with 503 so a
 * misconfigured deploy cannot accidentally expose the docs.
 */
class DocsBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = config('scramble.docs_user');
        $expectedPass = config('scramble.docs_password');

        if (empty($expectedPass)) {
            return response('API docs not available', 503);
        }

        if (
            $request->getUser() === $expectedUser
            && hash_equals((string) $expectedPass, (string) $request->getPassword())
        ) {
            return $next($request);
        }

        return response('Authentication required', 401, [
            'WWW-Authenticate' => 'Basic realm="MART API Docs"',
        ]);
    }
}
