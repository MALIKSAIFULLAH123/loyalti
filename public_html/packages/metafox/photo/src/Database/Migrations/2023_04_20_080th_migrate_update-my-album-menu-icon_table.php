<?php

use Illuminate\Database\Migrations\Migration;
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
                ['menu', '=', 'photo.sidebarMenu'],
                ['name', '=', 'my_albums'],
                ['resolution', '=', 'web'],
                ['icon', '=', 'ico-user-man-o'],
            ])
            ->first();

        if ($menuItem instanceof MenuItem) {
            $menuItem->update(['icon' => 'ico-photos-check']);
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
