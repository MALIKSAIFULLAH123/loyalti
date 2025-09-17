<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        /*
         * update core_menu_items set "to" = REPLACE("to", 'admincp/', '');
         * update core_seo_meta set "url" = REPLACE("url", 'admincp/', '');
         * update core_drivers set "url" = REPLACE("url", 'admincp/', '');
         * update core_admin_search set "url" = REPLACE("url", 'admincp/', '');
         */

        if (Schema::hasTable('core_menu_items')) {
            \MetaFox\Menu\Models\MenuItem::where('to', 'LIKE', '%admincp/%')
                ->update([
                    "to" => DB::raw("REPLACE('to','admincp/','')")
                ]);
        }

        if (Schema::hasTable('core_drivers')) {
            \MetaFox\Core\Models\Driver::where('url', 'LIKE', '%admincp/%')
                ->update([
                    "url" => DB::raw("REPLACE('url','admincp/','')")
                ]);
        }
        if (Schema::hasTable('core_seo_meta')) {
            \MetaFox\Core\Models\Driver::where('url', 'LIKE', '%admincp/%')
                ->update([
                    "url" => DB::raw("REPLACE('url','admincp/','')")
                ]);
        }
        if (Schema::hasTable('core_admin_search')) {
            \MetaFox\Core\Models\AdminSearch::where('url', 'LIKE', '%admincp/%')
                ->update([
                    "url" => DB::raw("REPLACE('url','admincp/','')")
                ]);
        }
    }
};
