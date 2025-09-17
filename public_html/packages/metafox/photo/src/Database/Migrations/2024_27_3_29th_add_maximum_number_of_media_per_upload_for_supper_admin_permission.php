<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Platform\UserRole;

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

        if (!Schema::hasTable('auth_role_has_value_permissions')) {
            return;
        }

        $permission = Permission::query()
            ->where('module_id', 'photo')
            ->where('entity_type', '=', 'photo')
            ->where('name', 'photo.maximum_number_of_media_per_upload')
            ->first();

        if (!$permission instanceof Permission) {
            return;
        }

        $permission->assignRoleWithPivot([UserRole::SUPER_ADMIN_USER_ID => 0]);
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
