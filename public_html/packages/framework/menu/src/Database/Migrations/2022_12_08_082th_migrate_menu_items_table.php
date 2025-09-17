<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (Schema::hasTable('core_menu_items')) {
            DbTableHelper::deleteDuplicatedRows('core_menu_items', 'id',
                ['name', 'menu', 'parent_name', 'resolution']);
            \Illuminate\Support\Facades\DB::statement("update core_menu_items set parent_name='' where parent_name is null");
        }
    }
};
