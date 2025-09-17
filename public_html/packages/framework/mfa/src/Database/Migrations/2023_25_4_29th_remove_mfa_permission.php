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
 * @link \$PACKAGE_NAMESPACE$\Models\
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

        $permissionIds = Permission::query()
            ->where('module_id', 'mfa')
            ->where('entity_type', '<>', '*')
            ->pluck('id')
            ->toArray();

        DB::table('auth_role_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        Permission::query()
            ->whereIn('id', $permissionIds)
            ->delete();
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
