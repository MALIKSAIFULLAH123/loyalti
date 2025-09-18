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

        $this->updateWebMenus();

        $this->updateMobileMenus();
    }

    protected function updateWebMenus(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_WEB];

        $menus = ['sidebarMenu'];

        $names = ['all_song'];

        $icons = [
            'all_song' => 'ico-music-allsongs',
        ];

        $updates = $this->buildPayload($resolutions, $menus, $names, $icons);

        $this->updateMenuItems($updates);
    }

    protected function updateMobileMenus(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_MOBILE];

        $menus = ['sidebarMenu'];

        $names = ['all'];

        $icons = [
            'all' => 'ico-music-allsongs',
        ];

        $updates = $this->buildPayload($resolutions, $menus, $names, $icons);

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

    protected function buildPayload(array $resolutions, array $menus, array $names, array $icons): array
    {
        $updates = [];

        foreach ($resolutions as $resolution) {
            foreach ($menus as $menu) {
                foreach ($names as $name) {
                    $updates[] = [
                        'where' => [
                            'menu'       => sprintf('%s.%s', 'music', $menu),
                            'name'       => $name,
                            'resolution' => $resolution,
                            'parentName' => '',
                        ],
                        'update' => [
                            'icon' => Arr::get($icons, $name),
                        ],
                    ];
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
