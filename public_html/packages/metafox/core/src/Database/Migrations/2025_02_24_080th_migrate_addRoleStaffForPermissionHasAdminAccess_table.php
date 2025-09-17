<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Platform\UserRole;

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
        $permission = \MetaFox\Authorization\Models\Permission::query()
            ->where('name', 'admincp.has_admin_access')
            ->first();

        if (!$permission) {
            return;
        }
        
        $roles = UserRole::LEVEL_STAFF;
        $permission->assignRole($roles);

        resolve(PermissionRepositoryInterface::class)->initializeDefaultPermissionForCustomRoles($permission, $roles);
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('addRoleStaffForPermissionHasAdminAccess');
    }
};
