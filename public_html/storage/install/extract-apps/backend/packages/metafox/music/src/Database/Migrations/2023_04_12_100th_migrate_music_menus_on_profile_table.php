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

        $this->updateMenuLabels();
    }

    protected function updateMenuLabels(): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE];

        $menus = ['user.user.profileMenu', 'page.page.profileMenu', 'group.group.profileMenu'];

        $actionNames = ['music'];

        $labels = [
            'music' => 'music::phrase.music',
        ];

        $updates = $this->buildPayload($resolutions, $menus, $actionNames, $labels);

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

    protected function buildPayload(array $resolutions, array $menus, array $actionNames, array $labels): array
    {
        $updates = [];

        foreach ($resolutions as $resolution) {
            foreach ($menus as $menu) {
                foreach ($actionNames as $actionName) {
                    $updates[] = [
                        'where' => [
                            'menu'       => $menu,
                            'name'       => $actionName,
                            'resolution' => $resolution,
                            'parentName' => '',
                        ],
                        'update' => [
                            'label' => Arr::get($labels, $actionName),
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
