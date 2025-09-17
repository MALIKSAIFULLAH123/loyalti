<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('photo_groups', 'total_photo')) {
            Schema::table('photo_groups', function (Blueprint $table) {
                $table->dropColumn('total_photo');
            });
        }

        if (!Schema::hasTable('photo_collection_statistic')) {
            Schema::create('photo_collection_statistic', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::totalColumns($table, ['photo', 'video']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('photo_groups', 'total_photo')) {
            Schema::table('photo_groups', function (Blueprint $table) {
                DbTableHelper::totalColumns($table, ['photo']);
            });
        }
        Schema::dropIfExists('photo_collection_statistic');
    }
};
