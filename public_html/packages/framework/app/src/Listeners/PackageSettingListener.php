<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\App\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\App\Jobs\VerifyStoreInformation;
use MetaFox\App\Models\Package;
use MetaFox\App\Policies\PackagePolicy;
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
    public function getPolicies(): array
    {
        return [
            Package::class => PackagePolicy::class,
        ];
    }

    public function getSiteSettings(): array
    {
        return [];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(VerifyStoreInformation::class)
            ->everyTwoHours()
            ->withoutOverlapping();
    }

    public function getEvents(): array
    {
        return [
            'package.is_active' => [
                PackageActiveListener::class,
            ],
            'package.get_eloquent_builder' => [
                GetPackageEloquentBuilderListener::class,
            ],
            'metafox:installed' => [
                MetaFoxInstalledListener::class,
            ],
        ];
    }
}
