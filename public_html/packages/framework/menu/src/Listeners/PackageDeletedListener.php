<?php

namespace MetaFox\Menu\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
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
            $this->cleanupMenu($package);
            $this->cleanupMenuItem($package);
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }

    private function cleanupMenu(string $package): void
    {
        Log::channel('installation')->info(sprintf('cleanupMenu  "%s"', $package));
        try {
            resolve(MenuRepositoryInterface::class)->getModel()
                ->newQuery()
                ->where(['module_id' => PackageManager::getAlias($package)])
                ->delete();
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }

    private function cleanupMenuItem(string $package): void
    {
        Log::channel('installation')->info(sprintf('cleanupMenuItem  "%s"', $package));
        try {
            resolve(MenuItemRepositoryInterface::class)->getModel()
                ->newQuery()
                ->where(['module_id' => PackageManager::getAlias($package)])
                ->delete();
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }
}
