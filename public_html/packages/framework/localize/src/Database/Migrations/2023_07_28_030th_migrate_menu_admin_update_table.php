<?php

use Illuminate\Support\Facades\DB;
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
        $menuItems = MenuItem::query()->getModel()
            ->where([
                'module_id' => 'localize',
                'menu'      => 'localize.admin',
            ])
            ->whereNotIn('name', ['settings', 'country', 'add_country'])
            ->orderBy('ordering')
            ->get();
        foreach ($menuItems as $menuItem) {
            $menuItem->update(['ordering' => DB::raw('ordering + 1')]);
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
