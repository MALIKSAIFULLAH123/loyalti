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

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $roleTableName  = config('permission.table_names.roles');
        if (!Schema::hasTable($roleTableName)) {
            return;
        }

        // should reserve id for special roles
        if (DB::getDefaultConnection() === 'pgsql') {
            DB::statement("ALTER SEQUENCE {$roleTableName}_id_seq RESTART WITH 100;");
        } else {
            DB::statement("ALTER TABLE $roleTableName AUTO_INCREMENT = 100;");
        }
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
