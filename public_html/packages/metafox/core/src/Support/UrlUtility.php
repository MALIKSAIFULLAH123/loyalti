<?php

namespace MetaFox\Core\Support;

use MetaFox\Platform\Contracts\UrlUtilityInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\UserEntity;

class UrlUtility implements UrlUtilityInterface
{
    public function makeApiUrl(string $route): string
    {
        return '/' . trim($route, '/');
    }

    public function getFromUserName(string $userName): array
    {
        /** @var UserEntity $user */
        $user = UserEntity::query()->where('user_name', '=', $userName)->firstOrFail();

        $entityId   = $user->entityId();
        $entityType = $user->entityType();
        $prefix     = $user->entityType();

        return [
            'path'      => "/$prefix/$entityId",
            'routeName' => 'viewItemDetail',
            'params'    => [
                'module_name'   => $entityType,
                'resource_name' => $entityType,
                'id'            => $user->entityId(),
            ],
        ];
    }

    public function makeApiResourceUrl(string $resourceName, int $resourceId): string
    {
        return sprintf('/%s/%s', $resourceName, $resourceId);
    }

    public function makeApiResourceFullUrl(string $resourceName, int $resourceId): string
    {
        $frontendRoot = config('app.url');
        $url          = $this->makeApiResourceUrl($resourceName, $resourceId);

        return $frontendRoot . $url;
    }

    public function makeApiFullUrl(string $uri): string
    {
        $frontendRoot = config('app.url');

        $uri = ltrim($uri, '/');

        return sprintf('%s/%s', $frontendRoot, $uri);
    }

    public function makeApiMobileUrl(string $route): string
    {
        return '/' . trim($route, '/');
    }

    public function makeApiMobileResourceUrl(string $resourceName, int $resourceId): string
    {
        return sprintf('/%s/%s', $resourceName, $resourceId);
    }

    public function convertUrlToLink(string $url, bool $isMobile = false): string
    {
        $frontendRoot = config('app.url');

        $url = str_replace($frontendRoot, MetaFoxConstant::EMPTY_STRING, $url);

        $url = trim($url, '/');

        if ($isMobile) {
            return $this->makeApiMobileUrl($url);
        }

        return $this->makeApiUrl($url);
    }

    public function isAbsoluteUrl(string $url): bool
    {
        extract(parse_url($url));

        return isset($host) && isset($scheme);
    }

    public function isAppUrl(string $url): bool
    {
        $inputUrlComponents = parse_url($url);
        $appUrlComponents   = parse_url(config('app.url'));

        $inputHost = $inputUrlComponents['host'] ?? '';
        $appHost   = $appUrlComponents['host'] ?? '';

        return mb_strtolower($inputHost) === mb_strtolower($appHost);
    }
}
