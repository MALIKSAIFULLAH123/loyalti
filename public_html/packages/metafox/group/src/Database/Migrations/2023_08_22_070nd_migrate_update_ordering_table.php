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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'feed.feed.itemActionMenu',
            'name' => 'mark_as_announcement',
        ])->update([
            'ordering' => 17,
        ]);
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'feed.feed.itemActionMenu',
            'name' => 'remove_announcement',
        ])->update([
            'ordering' => 17,
        ]);
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'feed.feed.itemActionMenu',
            'name' => 'save',
        ])->update([
            'ordering' => 18,
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
        Schema::dropIfExists('update_ordering');
    }
};
