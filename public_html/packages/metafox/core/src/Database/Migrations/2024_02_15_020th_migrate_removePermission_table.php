<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Models\Permission;

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
        if (!Schema::hasTable('auth_permissions')) {
            return;
        }

        if (!Schema::hasTable('auth_role_has_permissions')) {
            return;
        }

        $permission = Permission::query()
            ->where('name', 'link.approve')
            ->first();

        if (!$permission instanceof Permission) {
            return;
        }

        DB::table('auth_role_has_permissions')
            ->where('permission_id', $permission->id)
            ->delete();

        $permission->delete();

        // to do here
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
