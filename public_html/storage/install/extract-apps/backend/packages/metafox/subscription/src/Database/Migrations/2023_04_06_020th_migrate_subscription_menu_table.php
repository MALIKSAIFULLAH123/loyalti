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

        $item = $repository->getMenuItemByName('core.primaryMenu', 'subscription', $resolution, '');

        if (null === $item) {
            return;
        }

        $repository->updateMenuItem($item->entityId(), [
            'label' => 'subscription::phrase.subscriptions',
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
