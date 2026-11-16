<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Basic Auth guard for the API docs (/docs/api).
 *
 * Whether it applies at all is decided by API_DOCS_PROTECT_ACCESS, so the docs
 * can be read freely on a local machine without weakening a deployment. The
 * flag defaults to protected: an environment that forgets to set it asks for
 * credentials rather than serving the API surface to anyone.
 *
 * With protection on, credentials come from config/scramble.php
 * (API_DOCS_USER / API_DOCS_PASSWORD), and a missing password fails closed with
 * 503 so a half-configured deploy cannot accidentally expose the docs.
 */
class DocsBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('scramble.docs_protect_access', true)) {
            return $next($request);
        }

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
