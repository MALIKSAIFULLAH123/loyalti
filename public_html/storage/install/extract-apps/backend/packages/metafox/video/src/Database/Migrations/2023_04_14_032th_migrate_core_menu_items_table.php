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

        MenuItem::query()->where([
            'menu' => 'video.sidebarMenu',
            'name' => 'landing',
        ])->update([
            'icon' => 'ico-video-player-o',
        ]);

        MenuItem::query()->where('name', 'videos')
            ->whereIn('menu', [
                'core.dropdownMenu', 'core.primaryMenu',
            ])->update([
                'icon' => 'ico-video-player',
            ]);

        MenuItem::query()->where([
            'module_id'  => 'video',
            'resolution' => 'mobile',
            'icon'       => 'videocam',
        ])->update([
            'icon' => 'video-player-o',
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
