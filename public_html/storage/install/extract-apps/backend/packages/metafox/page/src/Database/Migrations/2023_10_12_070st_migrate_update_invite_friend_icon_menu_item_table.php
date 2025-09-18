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
            'menu' => 'page.page.profileActionMenu',
            'name' => 'invite',
        ])->update(['icon' => 'ico-envelope']);
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
