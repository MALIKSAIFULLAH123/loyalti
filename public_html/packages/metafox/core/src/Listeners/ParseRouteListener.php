<?php

namespace MetaFox\Core\Listeners;

class ParseRouteListener
{
    public function handle(string $url): ?array
    {
        if ($url == '/' || $url == '') {
            return [
                'path' => 'home',
            ];
        }

        return null;
    }
}
