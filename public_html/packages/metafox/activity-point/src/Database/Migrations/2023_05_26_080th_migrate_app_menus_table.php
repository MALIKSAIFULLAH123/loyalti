<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        $repository = resolve(\MetaFox\Menu\Repositories\MenuItemRepositoryInterface::class);

        $item = $repository->getMenuItemByName('activitypoint.sidebarMenu', 'package_transactions', \MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB, '');

        if (!$item) {
            return;
        }

        if ($item->icon != 'ico-box-o') {
            return;
        }

        $repository->updateMenuItem($item->entityId(), [
            'icon' => 'ico-refresh-o',
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
