<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\BackgroundStatus\Listeners;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * Class PackageSettingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'background-status.get_bg_status_image' => [
                GetBgStatusImageListener::class,
            ],
            'background-status.get_bg_status' => [
                GetBgStatusListener::class,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            BgsCollection::class => BgsCollectionPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
        ];
    }
}
