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
            'menu'       => 'saved.sidebarMenu',
            'name'       => 'all',
            'resolution' => 'mobile',
        ])->update(['to' => '/saved/all']);

        MenuItem::query()->where([
            'menu'       => 'saved.sidebarMenu',
            'name'       => 'my',
            'resolution' => 'mobile',
        ])->update(['to' => '/saved/my']);
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
