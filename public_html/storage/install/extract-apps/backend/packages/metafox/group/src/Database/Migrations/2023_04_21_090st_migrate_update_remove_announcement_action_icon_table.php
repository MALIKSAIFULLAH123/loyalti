<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;
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

        $repository = resolve(MenuItemRepositoryInterface::class);
        $menuItem   = $repository->getModel()
                    ->newModelQuery()
                    ->where([
                        ['menu', '=', 'feed.feed.itemActionMenu'],
                        ['name', '=', 'remove_announcement'],
                        ['resolution', '=', 'web'],
                        ['icon', '=', 'ico-trash-o'],
                    ])
                    ->first();

        if ($menuItem instanceof MenuItem) {
            $menuItem->update(['icon' => 'ico-close']);
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
