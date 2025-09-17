<?php

use MetaFox\Platform\MetaFoxConstant;
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

        $menus       = ['user.user.itemActionMenu', 'user.user.profilePopoverMenu', 'user.user.detailActionMenu'];
        $resolutions = [MetaFoxConstant::RESOLUTION_MOBILE, MetaFoxConstant::RESOLUTION_WEB];

        MenuItem::query()
            ->where('name', 'edit_list')
            ->whereIn('menu', $menus)
            ->whereIn('resolution', $resolutions)
            ->update(['label' => 'user::phrase.move_to_friends_list']);
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
