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
        MenuItem::query()->where([
            'menu'       => 'saved.saved_list.detailActionMenu',
            'name'       => 'delete',
            'resolution' => 'mobile',
        ])->update([
            'ordering' => 10,
        ]);
        MenuItem::query()->where([
            'menu'       => 'saved.saved_list.itemActionMenu',
            'name'       => 'delete',
            'resolution' => 'mobile',
        ])->update([
            'ordering' => 10,
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
    }
};
