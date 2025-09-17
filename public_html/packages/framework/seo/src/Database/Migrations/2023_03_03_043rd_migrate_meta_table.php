<?php

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
        if (!Schema::hasTable('core_seo_meta')) {
            return;
        }

        if (!Schema::hasColumn('core_seo_meta', 'item_type')) {
            Schema::table('core_seo_meta', function (Blueprint $table) {
                $table->string('item_type')->nullable(true);
            });
        }

        if (!Schema::hasColumn('core_seo_meta', 'page_type')) {
            Schema::table('core_seo_meta', function (Blueprint $table) {
                $table->string('page_type')->nullable(true);
            });
        }

        if (!Schema::hasColumn('core_seo_meta', 'custom_sharing_route')) {
            Schema::table('core_seo_meta', function (Blueprint $table) {
                $table->unsignedTinyInteger('custom_sharing_route')->default(0);
            });
        }

        // to do here
    }
};
