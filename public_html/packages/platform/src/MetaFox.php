<?php

namespace MetaFox\Platform;

use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class PHPFox.
 */
class MetaFox
{
    /**
     * Get the phpFox version.
     *
     * @return string
     */
    public static function getVersion()
    {
        return MetaFoxConstant::VERSION;
    }

    /**
     * Get the phpFox product build.
     *
     * @return string
     */
    public static function getProductBuild()
    {
        return MetaFoxConstant::PRODUCT_BUILD;
    }

    /**
     * Check is trial.
     *
     * @return bool
     */
    public static function isTrial(): bool
    {
        return false;
    }

    public static function isMobile(): bool
    {
        return (bool) request()->headers->get('X-Mobile', false);
    }

    public static function clientDate(): string
    {
        return request()->headers->get('X-Date', Carbon::now());
    }

    public static function clientTimezone(): string
    {
        try {
            return Carbon::parse(self::clientDate())->timezoneName;
        } catch (\Throwable $exception) {
            return Carbon::now()->timezoneName;
        }
    }

    public static function clientTheme(): ?string
    {
        return request()->headers->get('X-Theme');
    }

    public static function getApiVersion(): ?string
    {
        return ResourceGate::getVersion() ?? MetaFoxConstant::DEFAULT_API_VERSION;
    }

    public static function getResolution(): string
    {
        if (self::isMobile()) {
            return MetaFoxConstant::RESOLUTION_MOBILE;
        }

        return MetaFoxConstant::RESOLUTION_WEB;
    }

    public static function getIpAddressRegex(): string
    {
        return sprintf('(?:^%s$)|(?:^%s$)', MetaFoxConstant::IP_ADDRESS_V4_REGEX, MetaFoxConstant::IP_ADDRESS_V6_REGEX);
    }

    public static function getWildCardIpAddressRegex(): string
    {
        return sprintf('(?:^%s$)|(?:^%s$)', MetaFoxConstant::IP_ADDRESS_V4_REGEX_WILDCARD, MetaFoxConstant::IP_ADDRESS_V6_REGEX_WILDCARD);
    }

    /**
     * Define core packages.
     *
     * @return string[]
     */
    public static function coreModules(): array
    {
        return [
            'Privacy',
            'Core',
            'User',
            'Activity',
            'Friend',
            'Photo',
        ];
    }
}
