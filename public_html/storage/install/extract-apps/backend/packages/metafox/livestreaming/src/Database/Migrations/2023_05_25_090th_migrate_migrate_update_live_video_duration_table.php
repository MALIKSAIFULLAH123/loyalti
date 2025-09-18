<?php

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
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('livestreaming_live_videos') || Schema::getColumnType('livestreaming_live_videos', 'duration') != 'string') {
            return;
        }
        $dbDriver = config('database.default');
        match ($dbDriver) {
            'pgsql' => $this->pgSqlUp(),
            default => $this->sqlUp(),
        };
    }

    protected function sqlUp(): void
    {
        Schema::table('livestreaming_live_videos', function (Blueprint $table) {
            $table->decimal('duration', 14)->nullable()->change();
        });
    }

    protected function pgSqlUp(): void
    {
        $prefix = DB::getTablePrefix();
        $table  = $prefix ? $prefix . 'livestreaming_live_videos' : 'livestreaming_live_videos';

        $sql = 'ALTER TABLE %s ALTER COLUMN duration TYPE DECIMAL(14,2) USING duration::DECIMAL';

        DB::statement(sprintf($sql, $table));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
