<?php

use MetaFox\Menu\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;

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
        MenuItem::query()->getModel()
            ->whereIn('menu', ['notification.notification.itemActionMenu', 'notification.notification.detailActionMenu'])
            ->where([
                'resolution' => 'mobile',
                'name'       => 'delete',
            ])
            ->update([
                'value' => 'deleteItem',
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
