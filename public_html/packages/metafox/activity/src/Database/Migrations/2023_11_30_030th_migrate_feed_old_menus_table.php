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
        $this->removeFeedSidebarMenu();
        $this->removeFeedSidebarMenuItem();
    }

    private function removeFeedSidebarMenu(): void
    {
        if (!Schema::hasTable('core_menus')) {
            return;
        }

        \MetaFox\Menu\Models\Menu::query()
            ->where([
                'module_id' => 'activity',
                'package_id' => 'metafox/activity',
                'name' => 'feed.sidebarMenu',
                'resolution' => 'web',
            ])
            ->delete();
    }

    private function removeFeedSidebarMenuItem(): void
    {
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'activity',
                'package_id' => 'metafox/activity',
                'menu' => 'feed.sidebarMenu',
                'resolution' => 'web',
            ])
            ->delete();
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
