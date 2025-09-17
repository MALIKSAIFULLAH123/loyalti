<?php

namespace MetaFox\User\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\UserRole;

/**
 * Class AuthRoleTableSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class AuthRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedRoles();
    }

    /**
     * Seed default roles and assign to default admin.
     */
    public function seedRoles(): void
    {
        // maintain the special role integrities
        foreach (UserRole::ROLES as $roleId => $name) {
            $isRoleExists = Role::query()
                ->where('id', $roleId)
                ->where('is_special', 1)
                ->exists();

            if ($isRoleExists) {
                continue;
            }

            // override if the role was created incorrectly
            Role::updateOrCreate([
                'id' => $roleId,
            ], [
                'is_special' => 1,
                'guard_name' => 'api',
                'name'       => $name,
            ]);
        }
    }
}
