<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        MenuItem::query()
            ->where('menu', 'forum.sidebarMenu')
            ->where('package_id', 'metafox/forum')
            ->where('name', 'landing')
            ->update(['icon' => 'ico-comments-o']);

        MenuItem::query()
            ->where('menu', 'core.bodyMenu')
            ->where('package_id', 'metafox/forum')
            ->where('name', 'forums')
            ->update(['icon' => 'comments']);

        MenuItem::query()
            ->where('menu', 'core.dropdownMenu')
            ->where('package_id', 'metafox/forum')
            ->where('name', 'forum')
            ->update(['icon' => 'ico-comments']);

        MenuItem::query()
            ->where('menu', 'core.primaryMenu')
            ->where('package_id', 'metafox/forum')
            ->where('name', 'forum')
            ->update(['icon' => 'ico-comments']);
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
