<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ParseRedirectListener
{
    public function handle(...$params): ?array
    {
        if (count($params) === 0) {
            return null;
        }

        [$url] = $params;
        $url   = trim($url, '/');

        if (!Str::startsWith($url, 'redirect')) {
            return null;
        }

        $redirectedPath = app('events')->dispatch('core.parse_redirect_route', [str_replace('redirect/', '', $url)], true);
        if (is_array($redirectedPath) && Arr::has($redirectedPath, 'path')) {
            return array_merge($redirectedPath, ['redirect' => true]);
        }

        return null;
    }
}
