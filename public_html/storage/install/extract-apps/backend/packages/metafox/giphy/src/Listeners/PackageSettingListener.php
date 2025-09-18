<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Giphy\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'giphy_api_key' => [
                'value'     => '',
                'is_public' => false,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'giphy.get_gif_data' => [
                GetGifDataListener::class,
            ],
        ];
    }
}
