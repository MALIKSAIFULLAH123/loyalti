<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthenticateMirror
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, \Closure $next)
    {
        if (!$request->hasHeader('Authorization')) {
            $token = $request->get('access_token');

            if (!$token) {
                $cookieName = config('session.cookie_prefix') . 'token';
                $token      = $request->cookie($cookieName);
            }

            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token, true);
            }
        }

        return $next($request);
    }
}
