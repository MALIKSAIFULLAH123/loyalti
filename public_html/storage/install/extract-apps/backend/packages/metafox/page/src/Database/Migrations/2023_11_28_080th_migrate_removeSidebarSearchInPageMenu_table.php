<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Models\Menu;

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
        Menu::query()->where(['name' => 'page.sidebarSearchInPageMenu'])->delete();
        MenuItem::query()->where(['menu' => 'page.sidebarSearchInPageMenu'])->delete();
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
