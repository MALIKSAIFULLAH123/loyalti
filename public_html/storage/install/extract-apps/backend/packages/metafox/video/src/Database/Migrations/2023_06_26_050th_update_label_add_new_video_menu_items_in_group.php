<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;

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

        $needUpdateMenus = [
            'video.video.headerItemActionOnGroupProfileMenu',
            'video.sidebarMenu',
        ];

        MenuItem::query()
            ->where(['resolution' => 'web'])
            ->whereIn('menu', $needUpdateMenus)
            ->whereIn('name', ['video', 'add'])
            ->update([
                'label' => 'video::phrase.add_new_video',
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
