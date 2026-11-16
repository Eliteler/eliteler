<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust any proxy in front of the app (Cloudflare, nginx, load balancers),
     * so HTTPS that ends at the proxy is seen as HTTPS and paginator links
     * are not built with http://.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Host is left out so a forwarded host can't change generated URLs.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;
}
