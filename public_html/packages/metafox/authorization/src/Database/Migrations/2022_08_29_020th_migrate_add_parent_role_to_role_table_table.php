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
        $tableNames = config('permission.table_names');

        if (Schema::hasTable($tableNames['roles'])) {
            if (!Schema::hasColumn($tableNames['roles'], 'parent_id')) {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->unsignedBigInteger('parent_id')->default(0)->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (Schema::hasTable($tableNames['roles'])) {
            if (Schema::hasColumn($tableNames['roles'], 'parent_id')) {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->dropColumn(['parent_id']);
                });
            }
        }
    }
};
