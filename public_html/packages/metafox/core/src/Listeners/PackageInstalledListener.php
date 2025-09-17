<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Models\StatsContentType;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * Handle module installed.
 *
 * Class PackageInstalledListener
 */
class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $this->publishSiteSettings($package);
        $this->publishDrivers($package);
        $this->publishSiteStats($package);
    }

    /**
     * publish site settings into the database.
     *
     * @param string $package
     */
    private function publishSiteSettings(string $package): void
    {
        Log::channel('installation')->debug('publishSiteSettings', [$package]);

        /** @var null|BasePackageSettingListener $listener */
        $listener = PackageManager::getListener($package);

        if (!$listener) {
            return;
        }

        $moduleId = PackageManager::getAlias($package);

        $settings = $listener->getSiteSettings();

        //        Log::channel('installation')->debug('setupPackageSettings', $settings);

        Settings::setupPackageSettings($moduleId, $settings);
    }

    /**
     * Import drivers from "resources/drivers.php".
     *
     * @param string $package
     */
    private function publishDrivers(string $package): void
    {
        Log::channel('installation')->debug('publishDrivers', [$package]);

        $drivers = PackageManager::readFile($package, 'resources/drivers.php');

        if ($drivers) {
            resolve(DriverRepositoryInterface::class)->setupDrivers($package, $drivers);
        }
    }

    private function publishSiteStats(string $package): void
    {
        if ($package !== 'metafox/core') {
            return;
        }

        $data          = PackageManager::discoverSettings('getSiteStatContent');
        $modifiedTypes = StatsContentType::query()->where('is_modified', 1)->get()->pluck('is_modified', 'name')->toArray();
        $insertData    = [];

        foreach ($data as $config) {
            if (!is_array($config)) {
                continue;
            }

            foreach ($config as $itemType => $stat) {
                $isModified = Arr::get($modifiedTypes, $itemType, false);

                if ($isModified) {
                    continue;
                }

                // Old version compatible
                if (is_string($stat)) {
                    // $stat is icon
                    Arr::set($insertData, $itemType, [
                        'icon' => $stat,
                        'to'   => null,
                    ]);
                    continue;
                }

                $data = Arr::only($stat, ['icon', 'to']);
                Arr::set($data, 'name', $itemType);

                // Adding default icon, to
                $data = Arr::add($data, 'icon', 'ico-square-o');
                $data = Arr::add($data, 'to', null);
                $data = Arr::add($data, 'operation', Arr::get($stat, 'operation', MetaFoxConstant::OPERATION_AGGREGATE_FUNCTION_SUM));

                $insertData[] = $data;
            }
        }

        $insertData = array_chunk($insertData, 1000);
        foreach ($insertData as $insert) {
            StatsContentType::query()->upsert($insert, ['name'], ['icon', 'to', 'operation']);
        }
    }
}
