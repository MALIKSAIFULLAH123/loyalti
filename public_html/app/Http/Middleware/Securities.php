<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class Securities
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $securityConfigs = config('security', []);

        foreach ($securityConfigs as $key => $value) {
            $method = sprintf('%s%s', 'handle', Str::studly($key));
            if (!method_exists($this, $method)) {
                continue;
            }

            $this->$method($request, $response, $value);
        }

        return $response;
    }

    private function handleHeaderContentTypeOptions($request, $response, $value)
    {
        if (empty($value)) {
            return;
        }

        if (!method_exists($response, 'header')) {
            // prevent issue with non-http responses
            return;
        }

        $response->header('X-Content-Type-Options', $value);
    }

    private function handleHeaderAccessControlOrigin($request, $response, $value)
    {
        if (empty($value)) {
            return;
        }

        if (!method_exists($response, 'header')) {
            // prevent issue with non-http responses
            return;
        }

        $response->header('Access-Control-Allow-Origin', $value);

        if ($value != '*') {
            $response->header('Vary', 'Origin');
        }
    }
}
