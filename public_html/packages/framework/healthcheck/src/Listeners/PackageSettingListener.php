<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\HealthCheck\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\HealthCheck\Jobs\CheckQueueWorkJob;
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
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function registerApplicationSchedule(Schedule $schedule)
    {
        $schedule->job(CheckQueueWorkJob::class)
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    public function getCheckers(): array
    {
        return [
            \MetaFox\HealthCheck\Checks\CheckEnvironment::class,
            \MetaFox\HealthCheck\Checks\CheckServerLoad::class,
            \MetaFox\HealthCheck\Checks\CheckLogging::class,
            \MetaFox\HealthCheck\Checks\CheckCache::class,
            \MetaFox\HealthCheck\Checks\CheckDatabase::class,
            \MetaFox\HealthCheck\Checks\CheckQueueWorker::class,
            \MetaFox\HealthCheck\Checks\CheckReachableUrls::class,
            \MetaFox\HealthCheck\Checks\CheckFilesystemPermission::class,
        ];
    }

    public function getEvents(): array
    {
        return [
            'user.admin_signed_in' => [
                AdminSignedInListener::class,
            ],
            'user.signed_in'       => [
                UserSignedInListener::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'latest_heartbeat_at' => [
                'value'     => [
                    'default' => '',
                ],
                'is_public' => false,
            ],
        ];
    }
}
