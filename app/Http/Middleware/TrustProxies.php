<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Driven by TRUSTED_PROXIES (see config/utilities.php) rather than
     * hard-coded, so the same code runs locally with no proxy and behind the
     * university's TLS-terminating proxy without a code change.
     *
     * @var array|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $proxies = config('utilities.trustedProxies', '');

        if (! is_string($proxies)) {
            return;
        }

        $proxies = trim($proxies);

        // '*' / '**' are Laravel's own wildcards and must be passed through as
        // strings, not exploded into a one-element list.
        if ($proxies === '*' || $proxies === '**') {
            $this->proxies = $proxies;

            return;
        }

        if ($proxies !== '') {
            $this->proxies = array_values(array_filter(array_map('trim', explode(',', $proxies))));
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
