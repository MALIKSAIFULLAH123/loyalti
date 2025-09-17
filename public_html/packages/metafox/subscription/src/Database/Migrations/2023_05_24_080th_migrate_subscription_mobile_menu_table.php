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

        /**
         * @var MenuItemRepositoryInterface $repository
         */
        $repository = resolve(MenuItemRepositoryInterface::class);

        if (null === $repository) {
            return;
        }

        $item = $repository->getMenuItemByName('core.bodyMenu', 'membership', MetaFoxConstant::RESOLUTION_MOBILE, '');

        if (null === $item) {
            return;
        }

        $repository->updateMenuItem($item->entityId(), [
            'label' => 'subscription::phrase.subscriptions',
            'name'  => 'subscription',
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
