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
        MenuItem::query()->where([
            'name'       => 'save',
            'resolution' => 'mobile',
        ])->whereIn('menu', ['forum.forum_post.detailActionMenu', 'forum.forum_thread.detailActionMenu'])
            ->update([
                'value' => 'saveItemDetail',
            ]);

        MenuItem::query()->where([
            'resolution' => 'mobile',
            'name'       => 'unsave',
        ])->whereIn('menu', ['forum.forum_post.detailActionMenu', 'forum.forum_thread.detailActionMenu'])
            ->update([
                'value' => 'undoSaveItemDetail',
            ]);

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('updateMenuItemDetailOfSaved');
    }
};
