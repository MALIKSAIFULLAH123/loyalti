<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('activity_schedules')) {
            return;
        }

        match (database_driver()) {
            'mysql' => $this->migrateMysql(),
            'pgsql' => $this->migratePostgres(),
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    private function migrateMysql()
    {
        return DB::statement('ALTER TABLE `activity_schedules` MODIFY `schedule_time` timestamp NULL');
    }

    private function migratePostgres()
    {
        return DB::statement('ALTER TABLE "activity_schedules" ALTER COLUMN "schedule_time" DROP NOT NULL');
    }
};
