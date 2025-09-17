<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Menu\Models\MenuItem;
use Illuminate\Support\Facades\Schema;

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
                'name'   => 'emoney',
                'update' => [
                    'ordering' => 2,
                ],
            ],
            [
                'name'   => 'activity_points',
                'update' => [
                    'ordering' => 3,
                ],
            ],
            [
                'name'   => 'manage_friends',
                'update' => [
                    'ordering' => 4,
                ],
            ],
            [
                'name'   => 'manage_hidden',
                'update' => [
                    'ordering' => 5,
                ],
            ],
            [
                'name'   => 'language',
                'update' => [
                    'label'    => 'localize::phrase.language',
                    'icon'     => 'ico-language',
                    'ordering' => 6,
                ],
            ],
            [
                'name'   => 'dark_mode',
                'update' => [
                    'ordering' => 7,
                ],
            ],
            [
                'name'   => 'chooseThemes',
                'update' => [
                    'label'    => 'layout::phrase.theme_variant',
                    'icon'     => 'ico-desktop-text',
                    'ordering' => 8,
                ],
            ],
            [
                'name'   => 'toggle-control',
                'update' => [
                    'ordering' => 9,
                ],
            ],
            [
                'name'   => 'admincp',
                'update' => [
                    'ordering' => 10,
                ],
            ],
            [
                'name'   => 'logout',
                'update' => [
                    'ordering' => 11,
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
