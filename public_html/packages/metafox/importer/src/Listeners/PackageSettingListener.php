<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Importer\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Importer\Jobs\ImportMonitor;
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
    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(ImportMonitor::class)->everyMinute()->withoutOverlapping();
    }

    public function getSiteSettings(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [
            'importer.completed' => [
                ImporterCompleted::class,
            ],
            'parseRoute' => [
                EventRouteListener::class,
            ],
        ];
    }
}
