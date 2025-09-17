<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use Illuminate\Contracts\Translation\HasLocalePreference;
use MetaFox\Platform\Notifications\Notification;

/**
 * Interface IsNotifiable.
 */
interface IsNotifiable extends Entity, HasLocalePreference
{
    public function notificationEmail(): string;

    public function notificationPhoneNumber(): string;

    public function notificationUserName(): string;

    public function notificationFullName(): string;

    /**
     * @param  string       $driver
     * @param  Notification $notification
     * @return mixed
     */
    public function routeNotificationFor(string $driver, Notification $notification);
}
