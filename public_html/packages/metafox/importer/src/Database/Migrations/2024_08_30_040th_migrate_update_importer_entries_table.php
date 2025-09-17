<?php

use Illuminate\Support\Facades\DB;
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
        if (!Schema::hasTable('importer_entries') || Schema::getColumnType('importer_entries', 'ref_id') != 'string') {
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
        Schema::table('importer_entries', function (Blueprint $table) {
            $table->string('ref_id', 128)->change();
        });
    }

    protected function pgSqlUp(): void
    {
        $prefix = DB::getTablePrefix();
        $table  = $prefix ? $prefix . 'importer_entries' : 'importer_entries';

        $sql = 'ALTER TABLE %s ALTER COLUMN ref_id TYPE VARCHAR(128) USING ref_id::VARCHAR';

        DB::statement(sprintf($sql, $table));
    }
};
