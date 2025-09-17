<?php

namespace MetaFox\Platform\Contracts;

interface UrlUtilityInterface
{
    /**
     * @param  string $route
     * @return string
     */
    public function makeApiUrl(string $route): string;

    /**
     * @param string $userName
     *
     * @return array<mixed>
     */
    public function getFromUserName(string $userName): array;

    /**
     * @param  string $resourceName
     * @param  int    $resourceId
     * @return string
     */
    public function makeApiResourceUrl(string $resourceName, int $resourceId): string;

    /**
     * @param  string $resourceName
     * @param  int    $resourceId
     * @return string
     */
    public function makeApiResourceFullUrl(string $resourceName, int $resourceId): string;

    /**
     * @param  string $uri
     * @return string
     */
    public function makeApiFullUrl(string $uri): string;

    /**
     * @param  string $route
     * @return string
     */
    public function makeApiMobileUrl(string $route): string;

    /**
     * @param  string $resourceName
     * @param  int    $resourceId
     * @return string
     */
    public function makeApiMobileResourceUrl(string $resourceName, int $resourceId): string;

    /**
     * @param  string $url
     * @param  bool   $isMobile
     * @return string
     */
    public function convertUrlToLink(string $url, bool $isMobile = false): string;

    /**
     * check if the current url is a absolute url or not.
     * @param string $url
     *
     * @return bool
     */
    public function isAbsoluteUrl(string $url): bool;

    /**
     * @param  string $url
     * @return bool
     */
    public function isAppUrl(string $url): bool;
}
