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
            'menu'       => 'event.event.itemActionMenu',
            'name'       => 'report',
            'resolution' => 'web',
        ])->update([
            'ordering' => 13,
        ]);

        MenuItem::query()->where([
            'menu'       => 'event.event.itemActionMenu',
            'name'       => 'delete',
            'resolution' => 'web',
        ])->update([
            'ordering' => 14,
        ]);

        MenuItem::query()->where([
            'menu'       => 'event.event.itemActionMenu',
            'name'       => 'report',
            'resolution' => 'mobile',
        ])->update([
            'ordering' => 16,
        ]);

        MenuItem::query()->where([
            'menu'       => 'event.event.itemActionMenu',
            'name'       => 'delete',
            'resolution' => 'mobile',
        ])->update([
            'ordering' => 17,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('updateOderingItemMenuForEvent');
    }
};
