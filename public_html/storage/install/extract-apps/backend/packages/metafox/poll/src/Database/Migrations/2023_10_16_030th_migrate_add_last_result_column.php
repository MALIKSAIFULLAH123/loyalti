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
        if (!Schema::hasTable('poll_results')) {
            return;
        }

        Schema::table('poll_results', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_last')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('poll_results', 'is_last')) {
            return;
        }

        Schema::table('poll_results', function (Blueprint $table) {
            $table->dropColumn(['is_last']);
        });
    }
};
