<?php

use MetaFox\Platform\MetaFoxConstant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
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

        foreach ([MetaFoxConstant::RESOLUTION_MOBILE, MetaFoxConstant::RESOLUTION_WEB] as $resolution) {
            $this->updateMenu($resolution);
        }
    }

    protected function updateMenu(string $resolution): void
    {
        /**
         * @var MenuItemRepositoryInterface $repository
         */
        $repository = resolve(MenuItemRepositoryInterface::class);

        if (null === $repository) {
            return;
        }

        $name = match ($resolution) {
            MetaFoxConstant::RESOLUTION_WEB => 'all_song',
            default                         => 'all'
        };

        $item = $repository->getMenuItemByName('music.sidebarMenu', $name, $resolution, '');

        if (null === $item) {
            return;
        }

        $repository->updateMenuItem($item->entityId(), [
            'icon' => 'ico-music-circle-o',
        ]);
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
