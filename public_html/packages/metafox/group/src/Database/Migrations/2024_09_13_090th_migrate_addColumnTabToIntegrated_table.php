<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Models\IntegratedModule;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

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
        if (!Schema::hasTable('group_integrated_modules')) {
            return;
        }

        if (!Schema::hasColumn('group_integrated_modules', 'tab')) {
            Schema::table('group_integrated_modules', function (Blueprint $table) {
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
        if (!Schema::hasTable('group_integrated_modules')) {
            return;
        }

        if (Schema::hasColumn('group_integrated_modules', 'tab')) {
            Schema::table('group_integrated_modules', function (Blueprint $table) {
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
                ])->update(['tab' => Arr::get($extra, 'tab', $item['name'])]);
        }
    }
};
