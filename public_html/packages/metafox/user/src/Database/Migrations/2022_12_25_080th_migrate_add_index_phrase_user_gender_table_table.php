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
        if (Schema::hasTable('user_gender')) {
            if (Schema::hasColumn('user_gender', 'phrase')) {
                Schema::table('user_gender', function (Blueprint $table) {
                    $table->index(['phrase']);
                });
            }
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('user_gender')) {
            if (Schema::hasColumn('user_gender', 'phrase')) {
                Schema::table('user_gender', function (Blueprint $table) {
                    $table->dropIndex(['phrase']);
                });
            }
        }
    }
};
