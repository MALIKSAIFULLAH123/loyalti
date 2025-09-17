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
            'menu'       => 'user.user.profileActionMenu',
            'name'       => 'message',
            'resolution' => 'web',
        ])->update([
            'ordering' => 3,
        ]);

        MenuItem::query()->where([
            'menu'       => 'user.user.profileActionMenu',
            'name'       => 'block',
            'resolution' => 'web',
        ])->update([
            'ordering' => 15,
        ]);

        MenuItem::query()->where([
            'menu'       => 'user.user.profileActionMenu',
            'name'       => 'unblock',
            'resolution' => 'web',
        ])->update([
            'ordering' => 16,
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
