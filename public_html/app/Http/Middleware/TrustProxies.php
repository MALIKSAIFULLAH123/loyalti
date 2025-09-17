<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * Set trusted proxy IP addresses.
     *
     * Both IPv4 and IPv6 addresses are
     * supported, along with CIDR notation.
     *
     * The "*" character is syntactic sugar
     * within TrustedProxy to trust any proxy
     * that connects directly to your server,
     * a requirement when you cannot know the address
     * of your proxy (e.g. if using Rackspace balancers).
     *
     * The "**" character is syntactic sugar within
     * TrustedProxy to trust not just any proxy that
     * connects directly to your server, but also
     * proxies that connect to those proxies, and all
     * the way back until you reach the original source
     * IP. It will mean that $request->getClientIp()
     * always gets the originating client IP, no matter
     * how many proxies that client's request has
     * subsequently passed through.
     */

    /**
     * NGINX Setup for php request.
     *
     * proxy_set_header X-Real-IP $remote_addr;
     * proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
     * proxy_set_header Host $host;
     * proxy_set_header X-Forwarded-Proto $scheme;
     */
    protected function proxies()
    {
        if ($this->proxies) {
            return $this->proxies;
        }

        // multiple host proxies like '127.0.0.1,13333'
        $this->proxies = config('app.trusted_proxies');

        if (is_string($this->proxies) && !str_starts_with($this->proxies, '*')) {
            $this->proxies = explode(',', preg_replace('/\s+/m', '', $this->proxies));
        }

        return $this->proxies;
    }
}
