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

        $menuRepository = resolve(\MetaFox\Menu\Repositories\MenuItemRepositoryInterface::class);
        $invitedMenu    = $menuRepository->getModel()->newModelQuery()
            ->where('menu', 'page.sidebarMenu')
            ->where('name', 'invited')
            ->where('resolution', 'web')
            ->where('to', '=', '/pages/invited')
            ->first();

        if ($invitedMenu instanceof \MetaFox\Menu\Models\MenuItem) {
            $invitedMenu->update(['to' => '/page/invited']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('migrate_update_page_sidebar_menu_items');
    }
};
