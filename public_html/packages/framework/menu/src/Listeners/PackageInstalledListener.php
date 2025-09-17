<?php

namespace MetaFox\Menu\Listeners;

use Illuminate\Support\Facades\Log;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Platform\PackageManager;

class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $this->publishMenus($package);
        $this->publishMenuItems($package);
    }

    /**
     * Install package menu.
     *
     * @param string $package
     */
    private function publishMenus(string $package): void
    {
        Log::channel('installation')->debug('publishMenus', [$package]);

        $menus = PackageManager::readFile($package, 'resources/menu/menus.php');

        resolve(MenuRepositoryInterface::class)
            ->setupMenus($package, $menus);
    }

    /**
     * Install menu items.
     *
     * @param string $package
     */
    private function publishMenuItems(string $package): void
    {
        $repository = resolve(MenuItemRepositoryInterface::class);

        foreach (['web', 'admin', 'mobile'] as $type) {

            $filename = sprintf('resources/menu/%s.php', $type);

            $items = PackageManager::readFile($package, $filename, true);

            if (!$items) {
                continue;
            }

            Log::channel('installation')->debug('publishMenuItems', [$type, $package]);

            $repository->setupMenuItems($package, $type, $items);
        }
    }
}
