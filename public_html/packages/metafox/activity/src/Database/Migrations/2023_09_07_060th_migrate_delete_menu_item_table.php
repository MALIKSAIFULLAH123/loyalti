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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu'      => 'group.group.profileMenu',
            'name'      => 'home',
            'module_id' => 'group',
        ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu'      => 'page.page.profileMenu',
            'name'      => 'home',
            'module_id' => 'page',
        ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu'      => 'user.user.profileMenu',
            'name'      => 'home',
            'module_id' => 'user',
        ])->delete();

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
