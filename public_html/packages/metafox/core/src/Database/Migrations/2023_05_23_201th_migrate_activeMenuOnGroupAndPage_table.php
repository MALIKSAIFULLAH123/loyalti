<?php

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
        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'group.groupManagerSettings',
                'name' => 'group_menu_settings',
            ])->update(['is_active' => 1]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'group.groupManagerSettings',
                'name' => 'menu',
            ])->update(['is_active' => 1]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'page_menu_settings',
            ])->update(['is_active' => 1]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'menu',
            ])->update(['is_active' => 1]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'page_messages',
            ])->update(['is_active' => 0]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'inbox',
            ])->update(['is_active' => 0]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'page_menu_divider',
            ])->update(['is_active' => 1]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'page.pageSettingsMenu',
                'name' => 'privacy_divider',
            ])->update(['is_active' => 1]);

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('inactiveMenuOnGroupAndPage');
    }
};
