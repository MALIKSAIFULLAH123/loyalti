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
            ->where('menu', 'event.event.detailActionMenu')
            ->where('resolution', 'mobile')
            ->where('name', 'manage_pending_post')
            ->where('module_id', 'event')
            ->delete();
        MenuItem::query()
            ->where('menu', 'event.event.itemActionMenu')
            ->where('resolution', 'mobile')
            ->where('name', 'manage_pending_post')
            ->where('module_id', 'event')
            ->delete();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
    }
};
