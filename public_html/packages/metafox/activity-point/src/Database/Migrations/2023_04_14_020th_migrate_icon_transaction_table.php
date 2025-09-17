<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

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

        $icons = [
            'transactions_history' => 'ico-clock-o',
            'package_transactions' => 'ico-box-o',
        ];

        foreach ($icons as $name => $icon) {
            $item = $repository->getMenuItemByName('activitypoint.sidebarMenu', $name, $resolution, '');

            if (null === $item) {
                return;
            }

            $repository->updateMenuItem($item->entityId(), [
                'icon' => $icon,
            ]);
        }
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
