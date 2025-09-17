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
        if (!Schema::hasTable('activity_shares')) {
            return;
        }

        if (!Schema::hasColumns('activity_shares', ['context_item_id', 'context_item_type'])) {
            Schema::table('activity_shares', function (Blueprint $table) {
                DbTableHelper::morphColumn($table, 'context_item', true);
            });

            \MetaFox\Activity\Jobs\MigrateShareItemJob::dispatchSync();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $columns = ['context_item_id', 'context_item_type'];

        if (Schema::hasColumns('activity_shares', $columns)) {
            Schema::dropColumns('activity_shares', $columns);
        }
    }
};
