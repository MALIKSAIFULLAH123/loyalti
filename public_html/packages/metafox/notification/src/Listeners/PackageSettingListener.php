<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Notification\Http\Resources\v1\WebSetting;
use MetaFox\Notification\Jobs\ClearDeletedNotification;
use MetaFox\Notification\Models\Notification;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Policies\TypePolicy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'notification.get_notification_settings_by_channel' => [
                GetNotificationSettingsByChannelListener::class,
            ],
            'notification.update_email_notification_settings' => [
                UpdateNotificationSettingsListener::class,
            ],
            'core.badge_counter' => [
                GetNewNotificationCount::class,
            ],
            'notification.delete_notification_by_type_and_notifiable' => [
                DeleteNotifyByTypeAndNotifiableListener::class,
            ],
            'notification.delete_mass_notification_by_item' => [
                DeleteMassNotificationByItemListener::class,
            ],
            'notification.delete_notification_by_type_and_item' => [
                DeleteNotifyByTypeAndItemListener::class,
            ],
            'notification.delete_notification_by_items' => [
                DeleteNotificationByItemsListener::class,
            ],
            'models.notify.approved' => [
                ModelApprovedListener::class,
            ],
            'importer.completed' => [
                ImporterCompleted::class,
            ],
            'notification.delete_types' => [
                DeleteTypeAndTypeDataListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            //
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Notification::ENTITY_TYPE => [
                'view'      => UserRole::LEVEL_REGISTERED,
                'deleteOwn' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Type::class => TypePolicy::class,
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(ClearDeletedNotification::class)->dailyAt('00:00');
    }

    public function getSiteSettings(): array
    {
        return [
            'refresh_time' => ['value' => 3.0],
        ];
    }
}
