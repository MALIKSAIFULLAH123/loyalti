<?php

use MetaFox\Platform\MetaFoxConstant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        $this->updateDeleteOrdering();

        $this->updateAddToPlaylistOrderingOnMobile();

        $this->updateReportOrderingOnItemActionMenu();
    }

    protected function updateReportOrderingOnItemActionMenu(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_MOBILE, MetaFoxConstant::RESOLUTION_WEB];

        $resourceNames = ['music_song', 'music_album', 'music_playlist'];

        $menus = ['detailActionMenu', 'itemActionMenu', 'itemActionMenuOnPlaylist'];

        $actionNames = ['report'];

        $orderings = [
            'report' => 19,
        ];

        $updates = $this->buildPayload($resolutions, $resourceNames, $menus, $actionNames, $orderings);

        $this->updateMenuItems($updates);
    }

    protected function updateAddToPlaylistOrderingOnMobile(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_MOBILE];

        $resourceNames = ['music_song'];

        $menus = ['detailActionMenu', 'itemActionMenu', 'itemActionMenuOnPlaylist'];

        $actionNames = ['add_to_playlist'];

        $orderings = [
            'add_to_playlist' => 13,
        ];

        $updates = $this->buildPayload($resolutions, $resourceNames, $menus, $actionNames, $orderings);

        $this->updateMenuItems($updates);
    }

    protected function updateDeleteOrdering(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE];

        $resourceNames = ['music_song', 'music_album', 'music_playlist'];

        $menus = ['detailActionMenu', 'itemActionMenu'];

        $actionNames = ['delete'];

        $orderings = [
            'delete' => 20,
        ];

        $updates = $this->buildPayload($resolutions, $resourceNames, $menus, $actionNames, $orderings);

        $this->updateMenuItems($updates);
    }

    protected function updateMenuItems(array $updates): void
    {
        /**
         * @var MenuItemRepositoryInterface $repository
         */
        $repository = resolve(MenuItemRepositoryInterface::class);

        if (null === $repository) {
            return;
        }

        foreach ($updates as $update) {
            $item = $repository->getMenuItemByName(...Arr::get($update, 'where'));

            if (null === $item) {
                continue;
            }

            $repository->updateMenuItem($item->entityId(), Arr::get($update, 'update'));
        }
    }

    protected function buildPayload(array $resolutions, array $resourceNames, array $menus, array $actionNames, array $orderings): array
    {
        $updates = [];

        foreach ($resolutions as $resolution) {
            foreach ($resourceNames as $resourceName) {
                foreach ($menus as $menu) {
                    foreach ($actionNames as $actionName) {
                        $updates[] = [
                            'where' => [
                                'menu'       => sprintf('%s.%s.%s', 'music', $resourceName, $menu),
                                'name'       => $actionName,
                                'resolution' => $resolution,
                                'parentName' => '',
                            ],
                            'update' => [
                                'ordering' => Arr::get($orderings, $actionName),
                            ],
                        ];
                    }
                }
            }
        }

        return $updates;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
