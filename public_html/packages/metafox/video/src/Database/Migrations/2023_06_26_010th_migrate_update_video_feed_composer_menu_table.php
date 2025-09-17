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

        $parentsMenus = [
            'feed.feed.feedComposerMenu',
            'user.user.feedComposerMenu',
            'page.page.feedComposerMenu',
            'group.group.feedComposerMenu',
            'event.event.feedComposerMenu',
        ];

        MenuItem::query()
            ->whereIn('menu', $parentsMenus)
            ->where('name', 'compose_video')
            ->update(['label' => 'video::phrase.video_url']);
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
