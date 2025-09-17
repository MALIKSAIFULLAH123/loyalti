<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class Notify.
 * @method static array getChannels(IsNotifiable $notifiable, string $type)
 * @method static string getPhoneNumber(IsNotifiable $notifiable, string $type)
 * @method static array routeNotificationFor(IsNotifiable $notifiable, string $driver, Notification $notification)
 * @method static void  addHandler(string $type, string $class)
 * @method static string|null  getHandler(string $type)
 *
 * @link    \MetaFox\Notification\Repositories\NotificationManager;
 */
class Notify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Notify';
    }
}
