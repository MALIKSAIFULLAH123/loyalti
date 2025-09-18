<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Newsletter\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Newsletter\Jobs\NewsletterMonitor;
use MetaFox\Newsletter\Notifications\NewsletterNotification;
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
    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'newsletter_notification',
                'module_id'  => 'newsletter',
                'handler'    => NewsletterNotification::class,
                'title'      => 'newsletter::phrase.process_newsletter_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail'],
                'ordering'   => 1,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(NewsletterMonitor::class)
            ->everyMinute()
            ->withoutOverlapping();
    }

    public function getEvents(): array
    {
        return [
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
        ];
    }
}
