<?php

use Illuminate\Database\Migrations\Migration;
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
        MenuItem::query()
            ->where('menu', 'group.searchWebCategoryMenu')
            ->where('name', 'forum')
            ->delete();

        MenuItem::query()
            ->where('menu', 'group.mobileCategoryMenu')
            ->where('name', 'forum')
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
