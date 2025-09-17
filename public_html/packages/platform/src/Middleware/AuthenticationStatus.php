<?php

namespace MetaFox\Platform\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationStatus extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param Closure  $next
     * @param string[] ...$guards
     *
     * @return mixed
     *
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $tokenId  = Auth::user()?->token()?->id;
        $response = $next($request);

        if (!is_object($response)) {
            return $response;
        }

        if (!method_exists($response, 'header')) {
            return $response;
        }

        if (!$tokenId) {
            $response->header('Authentication-Status', 'revoked');
            return $response;
        }

        $response->header('Authentication-Status', 'active');
        
        return $response;
    }
}
