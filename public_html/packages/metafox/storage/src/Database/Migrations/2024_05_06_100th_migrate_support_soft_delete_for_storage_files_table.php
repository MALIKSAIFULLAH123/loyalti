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
        if (!Schema::hasTable('storage_files')) {
            return;
        }

        if (Schema::hasColumn('storage_files', 'deleted_at')) {
            return;
        }

        Schema::table('storage_files', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('storage_files', 'deleted_at')) {
            return;
        }

        Schema::table('storage_files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
