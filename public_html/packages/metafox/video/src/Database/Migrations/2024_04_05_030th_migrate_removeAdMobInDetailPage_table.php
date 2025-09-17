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
        if (!Schema::hasTable('ad_mob_pages')) {
            return;
        }

        $configPage = DB::query()->from('ad_mob_pages')
            ->where('module_id', 'video')
            ->where('package_id', 'metafox/video')
            ->where('path', '/video/:id')
            ->first();

        if (!$configPage) {
            return;
        }

        DB::query()->from('ad_mob_config_page_data')
            ->where('page_id', $configPage->id)
            ->delete();

        DB::query()->from('ad_mob_pages')
            ->where('id', $configPage->id)
            ->delete();
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
