<?php

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
        $this->changeAliasForMenuItems();
        $this->changeAliasForMenus();
        $this->changeAliasForDrivers();
        $this->changeAliasForSiteSettings();
        $this->changeAliasForSeos();
        $this->removeUnusedItems();
    }

    private function removeUnusedItems(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'core.adminSidebarMenu',
                'name' => 'emoney',
            ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'menu' => 'core.accountMenu',
                'name' => 'emoney',
            ])->delete();
    }

    private function changeAliasForSiteSettings(): void
    {
        \MetaFox\Core\Models\SiteSetting::query()
            ->where([
                'module_id' => 'emoney',
            ])
            ->update([
                'module_id' => 'ewallet',
            ]);
    }

    private function changeAliasForMenus(): void
    {
        \MetaFox\Menu\Models\Menu::query()
            ->where([
                'module_id' => 'emoney',
            ])
            ->update([
                'module_id' => 'ewallet',
            ]);
    }

    private function changeAliasForMenuItems(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'module_id' => 'emoney',
            ])
            ->update([
                'module_id' => 'ewallet',
            ]);
    }

    private function changeAliasForDrivers(): void
    {
        \MetaFox\Core\Models\Driver::query()
            ->where([
                'module_id' => 'emoney',
            ])
            ->update([
                'module_id' => 'ewallet',
            ]);
    }

    private function changeAliasForSeos(): void
    {
        \MetaFox\SEO\Models\Meta::query()
            ->where([
                'module_id' => 'emoney',
            ])
            ->update([
                'module_id' => 'ewallet',
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
