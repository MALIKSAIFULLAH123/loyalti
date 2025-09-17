<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Jobs\MigratePackageIdJob;

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
        if (Schema::hasColumns('core_stats_contents', ['package_id', 'module_id'])) {
            return;
        }

        Schema::table('core_stats_contents', function (Blueprint $table) {
            \MetaFox\Platform\Support\DbTableHelper::moduleColumn($table);
        });

        $this->migrateUp();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('core_stats_contents', ['package_id', 'module_id']);
    }

    protected function migrateUp()
    {
        MigratePackageIdJob::dispatch();
    }
};
