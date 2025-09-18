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
        if (!Schema::hasTable('group_requests')) {
            return;
        }

        if (!Schema::hasColumns('group_requests', ['reviewer_id', 'reviewer_type'])) {
            Schema::table('group_requests', function (Blueprint $table) {
                DbTableHelper::morphColumn($table, 'reviewer', true);
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
        if (!Schema::hasTable('group_requests')) {
            return;
        }

        if (Schema::hasColumns('group_requests', ['reviewer_id', 'reviewer_type'])) {
            Schema::table('group_requests', function (Blueprint $table) {
                $table->dropColumn(['reviewer_id', 'reviewer_type']);
            });
        }
    }
};
