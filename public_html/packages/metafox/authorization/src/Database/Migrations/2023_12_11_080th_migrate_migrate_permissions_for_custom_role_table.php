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
        if (!Schema::hasTable('auth_roles')) {
            return;
        }

        if (Schema::hasColumn('auth_roles', 'root_parent_id')) {
            return;
        }

        Schema::table('auth_roles', function (Blueprint $table) {
            $table->bigInteger('root_parent_id', false, true)
                ->default(0);
        });

        \MetaFox\Authorization\Jobs\MigrateRootParentIdJob::dispatchSync();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('auth_roles')) {
            return;
        }

        if (!Schema::hasColumn('auth_roles', 'root_parent_id')) {
            return;
        }

        Schema::dropColumns('auth_roles', ['root_parent_id']);
    }
};
