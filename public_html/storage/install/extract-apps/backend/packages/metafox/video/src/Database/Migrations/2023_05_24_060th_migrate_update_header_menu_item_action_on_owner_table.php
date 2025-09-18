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
            'video.video.headerItemActionOnUserProfileMenu',
            'video.video.headerItemActionOnPageProfileMenu',
            'video.video.headerItemActionOnGroupProfileMenu',
        ];

        MenuItem::query()
            ->where('package_id', 'metafox/video')
            ->where('resolution', 'web')
            ->where('name', 'video')
            ->whereIn('menu', $needUpdateMenus)
            ->update(['to' => '/video/share?owner_id=:id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('update_header_menu_item_action_on_owner');
    }
};
