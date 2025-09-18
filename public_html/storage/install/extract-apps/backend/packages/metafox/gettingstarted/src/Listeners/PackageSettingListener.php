<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\GettingStarted\Listeners;

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
            'enable_getting_started' => ['value' => true],
        ];
    }

    public function getEvents(): array
    {
        return [
            'user.registered' => [
                UserRegisteredListener::class,
            ],
            'user.attributes.extra' => [
                UserAttributesExtraListener::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
            ],
            'models.actions.pending' => [
                ModelPendingActionListener::class,
            ],
        ];
    }
}
