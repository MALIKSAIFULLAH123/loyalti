<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Listeners;

use Illuminate\Support\Carbon;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\ActivityPoint\Support\Facade\ActionType;

/**
 * Class PackageInstalledListener.
 * @ignore
 */
class PackageInstalledListener
{
    /**
     * @param string $package
     *
     * @throws \Throwable
     */
    public function handle(string $package): void
    {
        if (ActivityPoint::isCustomInstalled($package)) {
            $now         = Carbon::now();
            $defaultData = [
                'points'     => 0,
                'max_earned' => 0,
                'period'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            ActivityPoint::installCustomPointSettings($defaultData);

            ActionType::setupCustomActionTypes();
            ActionType::setupActionTypesInterpolateFromTransaction();
        }
    }
}
