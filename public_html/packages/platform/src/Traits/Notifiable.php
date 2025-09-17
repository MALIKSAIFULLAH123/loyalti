<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\NotificationManagerInterface;
use MetaFox\Platform\Notifications\Notification;

/**
 * Trait Notifiable.
 *
 * @mixin Model
 * @mixin IsNotifiable
 *
 * We don't use default RoutesNotifications of Laravel.
 * @see     RoutesNotifications
 */
trait Notifiable
{
    use HasDatabaseNotifications;

    /**
     * Send the given notification.
     *
     * @param  mixed $instance
     * @return void
     */
    public function notify($instance)
    {
        app(Dispatcher::class)->send($this, $instance);
    }

    /**
     * Send the given notification immediately.
     *
     * @param  mixed $instance
     * @return void
     */
    public function notifyNow($instance)
    {
        app(Dispatcher::class)->sendNow($this, $instance);
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @param string       $driver
     * @param Notification $notification
     *
     * @return mixed
     */
    public function routeNotificationFor(string $driver, Notification $notification)
    {
        /**
         * If a class has routeNotificationForDatabase, routeNotificationForMail => take priority to use it.
         */
        $method = 'routeNotificationFor' . Str::studly($driver);

        if (method_exists($this, $method)) {
            return $this->{$method}($notification);
        }

        return app(NotificationManagerInterface::class)->routeNotificationFor($this, $driver, $notification);
    }
}
