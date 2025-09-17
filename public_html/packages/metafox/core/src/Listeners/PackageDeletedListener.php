<?php

namespace MetaFox\Core\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\PackageManager;

/**
 * Class PackageDeletedListener.
 * Handle event "packages.deleted".
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageDeletedListener
{
    /**
     * @param string $package
     */
    public function handle(string $package): void
    {
        try {
            $this->cleanupDrivers($package);
            $this->cleanUpDatabase($package);
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }

    /**
     * @param string $package
     *
     * @return string[]
     */
    private function getResourceNames(string $package): array
    {
        return PackageManager::getResourceNames($package);
    }

    /**
     * @param string $package
     */
    private function cleanUpDatabase(string $package): void
    {
        $resourceNames = $this->getResourceNames($package);

        if (empty($resourceNames) || !is_array($resourceNames)) {
            return;
        }

        PrivacyStream::query()->whereIn('item_type', $resourceNames)->delete();
    }

    private function cleanupDrivers(string $package): void
    {
        Log::channel('installation')->info(sprintf('cleanupDrivers  "%s"', $package));
        try {
            resolve(DriverRepositoryInterface::class)->getModel()
                ->newQuery()
                ->where(['package_id' => $package])
                ->delete();
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }
}
