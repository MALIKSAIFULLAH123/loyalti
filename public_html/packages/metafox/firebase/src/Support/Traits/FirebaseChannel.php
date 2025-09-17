<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Firebase\Support\Traits;

use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;

/**
 * Trait FirebaseChannel.
 */
trait FirebaseChannel
{
    public function validateConfiguration(): bool
    {
        return (bool) app('firebase.fcm')->validatePushNotificationConfiguration();
    }

    public function getDeviceRepository(): DeviceRepositoryInterface
    {
        return resolve(DeviceRepositoryInterface::class);
    }
}
