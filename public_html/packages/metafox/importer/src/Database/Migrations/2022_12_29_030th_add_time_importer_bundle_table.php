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
        if (Schema::hasTable('importer_bundle')) {
            Schema::table('importer_bundle', function (Blueprint $table) {
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
            });
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
        if (Schema::hasColumns('importer_bundle', ['start_time', 'end_time'])) {
            Schema::table('importer_bundle', function (Blueprint $table) {
                $table->dropColumn(['start_time']);
                $table->dropColumn(['end_time']);
            });
        }
    }
};
