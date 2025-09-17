<?php

use Illuminate\Support\Str;

if (!function_exists('seo_sharing_view')) {
    function seo_sharing_view(string $resolution, string $nameOrUrl, mixed $type = null, mixed $id = null, Closure $callback = null)
    {
        return resolve(MetaFox\SEO\Repositories\MetaRepositoryInterface::class)
            ->getSeoSharingView($resolution, $nameOrUrl, $type, $id, $callback);
    }
}

if (!function_exists('human_readable_bytes')) {
    /**
     * Format bytes to kb, mb, gb, tb.
     *
     * @param  mixed  $size
     * @param  int    $precision
     * @return string
     */
    function human_readable_bytes(mixed $size, int $precision = 2)
    {
        if ($size > 0) {
            $size     = (int) $size;
            $base     = log($size) / log(1024);
            $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }
}

if (!function_exists('normalize_seo_meta_name')) {
    function normalize_seo_meta_name(string $name): string
    {
        return Str::of($name)
            ->replace('/', '.')
            ->replaceFirst('admincp.', 'admin.')
            ->replace('..', '.')
            ->replace('__', '_')
            ->trim('_')
            ->trim();
    }
}

if (!function_exists('normalize_seo_meta_phrase')) {
    function normalize_seo_meta_phrase(string $name): string
    {
        [$alias, $key] = explode('.', Str::replace('admin.', '', $name), 3);
        $resolution    = str_starts_with($name, 'admin.') ? 'admin' : 'web';
        $key           = implode('_', array_unique(explode('.', $key)));
        $key           = Str::of($key)->trim('_')->replace('__', '_');

        return sprintf(
            '%s::%s.%s',
            $alias,
            $resolution === 'admin' ? 'phrase' : 'seo',
            $key
        );
    }
}

if (!function_exists('is_seo_url_match')) {
    function is_seo_url_match(string $pattern, string $url): string
    {
        $urlPath = trim(parse_url($url, PHP_URL_PATH), '/');

        $patternParts = explode('/', $pattern);
        $urlPathParts = explode('/', $urlPath);

        foreach ($patternParts as $position => $part) {
            if (preg_match('/\{(\w+)\??\}/', $part, $matches)) {
                $isOptional = strpos($matches[0], '?') !== false;

                // If URL segment doesn't exist and it's optional, continue
                if (!isset($urlPathParts[$position]) && $isOptional) {
                    continue;
                }

                // If URL segment is missing and it's required, it's not a match
                if (isset($urlPathParts[$position])) {
                    continue;
                }

                return false;
            }

            if (!isset($urlPathParts[$position]) || $patternParts[$position] !== $urlPathParts[$position]) {
                return false;
            }
        }

        return true;
    }
}
