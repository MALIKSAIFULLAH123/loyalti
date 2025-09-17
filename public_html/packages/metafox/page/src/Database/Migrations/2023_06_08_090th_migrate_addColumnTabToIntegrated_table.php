<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Page\Models\IntegratedModule;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;

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
        if (!Schema::hasTable('page_integrated_modules')) {
            return;
        }

        if (!Schema::hasColumn('page_integrated_modules', 'tab')) {
            Schema::table('page_integrated_modules', function (Blueprint $table) {
                $table->string('tab')->nullable();
            });
        }

        $this->handleUpdateTab();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('page_integrated_modules')) {
            return;
        }
    
        if (Schema::hasColumn('page_integrated_modules', 'tab')) {
            Schema::table('page_integrated_modules', function (Blueprint $table) {
                $table->dropColumn(['tab']);
            });
        }
    }

    public function handleUpdateTab(): void
    {
        $menuItems = resolve(MenuItemRepositoryInterface::class)
            ->getMenuItemByMenuName(IntegratedModule::MENU_NAME, 'web');

        foreach ($menuItems as $item) {
            $extra = $item['extra'];

            IntegratedModule::query()
                ->where([
                    'package_id' => $item['package_id'],
                    'name'       => $item['name'],
                ])->update(['tab' => Arr::get($extra, 'tab')]);
        }
    }
};
