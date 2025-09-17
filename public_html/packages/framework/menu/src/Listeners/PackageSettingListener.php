<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Menu\Listeners;

use MetaFox\Platform\Contracts\PackageSettingListenerInterface;
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
class PackageSettingListener extends BasePackageSettingListener implements PackageSettingListenerInterface
{
    public function getModuleName()
    {
        return 'menu';
    }

    public function getEvents(): array
    {
        return [
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'packages.deleted' => [
                PackageDeletedListener::class,
            ],
            'menu.item.delete' => [
                DeleteMenuItemListener::class,
            ],
        ];
    }
}
