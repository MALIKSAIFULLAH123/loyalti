<?php

use MetaFox\Menu\Models\MenuItem;
use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        $pageSettingItems = [
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'page_settings',
                'ordering' => 1,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'page_info',
                'ordering' => 2,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'about_page',
                'ordering' => 3,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'schedule_post',
                'ordering' => 4,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'page_settings_divider',
                'ordering' => 5,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'privacy_settings',
                'ordering' => 6,
            ],
            [
                'menu'     => 'page.pageSettingsMenu',
                'name'     => 'permissions',
                'ordering' => 7,
            ],
            [
                'menu'      => 'page.pageSettingsMenu',
                'name'      => 'privacy_divider',
                'ordering'  => 8,
            ],
            [
                'menu'      => 'page.pageSettingsMenu',
                'name'      => 'page_menu_settings',
                'ordering'  => 9,
            ],
            [
                'menu'      => 'page.pageSettingsMenu',
                'name'      => 'menu',
                'ordering'  => 10,
            ],
            [
                'menu'      => 'page.pageSettingsMenu',
                'name'      => 'page_menu_divider',
                'ordering'  => 11,
            ],
        ];

        foreach ($pageSettingItems as $pageSettingItem) {
            MenuItem::query()->getModel()->where([
                'menu'          => $pageSettingItem['menu'],
                'name'          => $pageSettingItem['name'],
                'module_id'     => 'page',
                'resolution'    => 'web',
                ])->update(['ordering' => $pageSettingItem['ordering']]);
        }
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
