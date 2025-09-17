<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\StaticPage\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'validation.unique_slug' => [
                UniqueSlugListener::class,
            ],
            'parseRoute' => [
                ParseToRouteListener::class,
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['static_page'];
    }
}
