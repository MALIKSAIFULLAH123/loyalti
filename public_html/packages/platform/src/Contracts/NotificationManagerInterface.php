<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\Notifications\Notification;

/**
 * Interface NotificationSettingInterface.
 */
interface NotificationManagerInterface
{
    /**
     * Get notification channels.
     *
     * @param IsNotifiable $notifiable
     * @param string       $type
     *
     * @return string[]
     */
    public function getChannelsForNotifiableByType(IsNotifiable $notifiable, string $type): array;

    /**
     * This method will map to driver's repository.
     *
     * @param IsNotifiable $notifiable
     * @param string       $driver
     * @param Notification $notification
     *
     * @return mixed
     */
    public function routeNotificationFor(IsNotifiable $notifiable, string $driver, Notification $notification);
}
