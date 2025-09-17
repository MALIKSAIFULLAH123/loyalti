<?php
namespace MetaFox\EMoney\Listeners;

use Illuminate\Support\Str;

class ParseRouteListener
{
    public function handle(string $url): ?array
    {
        try {

            $parts = explode('/', $url);

            if (!count($parts)) {
                return null;
            }

            $first = array_shift($parts);

            if (!Str::startsWith($first, 'ewallet_')) {
                return null;
            }

            $first = Str::replace('ewallet', 'emoney', $first);

            array_unshift($parts, $first);

            return [
                'path' => '/' . implode('/', $parts),
            ];
        } catch (\Throwable $exception) {
            exit($exception->getMessage());
        }
    }
}
