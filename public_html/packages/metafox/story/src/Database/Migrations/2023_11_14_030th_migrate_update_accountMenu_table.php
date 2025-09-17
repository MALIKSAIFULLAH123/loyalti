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
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        $menuItems = [
            [
                'name'   => 'account_settings',
                'update' => [
                    'ordering' => 1,
                ],
            ],
            [
                'name'   => 'story_archive',
                'update' => [
                    'ordering' => 2,
                    'icon'     => 'ico-clock',
                ],
            ],
            [
                'name'   => 'emoney',
                'update' => [
                    'ordering' => 3,
                ],
            ],
        ];

        foreach ($menuItems as $menuItem) {
            MenuItem::query()
                ->where('menu', 'core.accountMenu')
                ->where('name', $menuItem['name'])
                ->update($menuItem['update']);
        }

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
