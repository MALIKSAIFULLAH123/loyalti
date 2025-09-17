<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Forum\Jobs\MigrateStatistic;

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
        if (!Schema::hasTable('forums')) {
            return;
        }

        $dispatchJob = false;

        Schema::table('forums', function (Blueprint $table) use (&$dispatchJob) {
            $columns = [];

            if (!Schema::hasColumn('forums', 'total_comment')) {
                $columns[] = 'comment';
            }

            if (!Schema::hasColumn('forums', 'total_sub')) {
                $columns[] = 'sub';
            }

            if (count($columns)) {
                DbTableHelper::totalColumns($table, $columns);
                $dispatchJob = true;
            }
        });

        if ($dispatchJob) {
            MigrateStatistic::dispatch();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('forums', ['total_comment', 'total_sub']);
    }
};
