<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Layout\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Layout\Jobs\CheckBuild;
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
        $schedule->job(CheckBuild::class)
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();
    }

    public function getEvents(): array
    {
        return [
            'packages.installed' => [PackageInstalledListener::class,]
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'mfox_site_theme' => [
                'value'       => 'a0:a0',
                'type'        => 'string',
                'config_name' => 'app.mfox_site_theme',
            ],
        ];
    }
}
