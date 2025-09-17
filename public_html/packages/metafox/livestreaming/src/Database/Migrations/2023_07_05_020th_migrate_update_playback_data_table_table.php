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
        if (!Schema::hasTable('livestreaming_playback_data') || Schema::getColumnType('livestreaming_playback_data', 'live_id') != 'string') {
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
        Schema::table('livestreaming_playback_data', function (Blueprint $table) {
            $table->unsignedBigInteger('live_id')->change();
        });
    }

    protected function pgSqlUp(): void
    {
        $prefix = DB::getTablePrefix();
        $table  = ($prefix ?: '') . 'livestreaming_playback_data';

        $sql = 'ALTER TABLE %s ALTER COLUMN live_id TYPE BIGINT USING live_id::BIGINT';

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
