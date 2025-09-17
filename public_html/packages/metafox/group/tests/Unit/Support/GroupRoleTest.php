<?php

namespace MetaFox\Group\Tests\Unit\Support;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\GroupRole;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupRoleTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testInstance(): array
    {
        $service = resolve(GroupRole::class);
        $user    = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(GroupRole::class, $service);

        $group = Group::factory()->setUser($user)->create();
        $this->assertInstanceOf(User::class, $group);

        return [$service, $user, $group];
    }

    /**
     * @depends testInstance
     *
     * @param array<mixed> $params
     */
    public function testGetGroupRolesByUser(array $params)
    {
        /**
         * @var GroupRole $service
         * @var User      $user
         * @var User      $group
         */
        [$service, $user, $group] = $params;

        $response = $service::getGroupRolesByUser($user, $group);

        $this->assertNotEmpty($response);

        foreach ($response as $role) {
            $this->assertIsString($role);
        }
    }

    /**
     * @depends testInstance
     *
     * @param array<mixed> $params
     */
    public function testGetGroupSettingPermission(array $params)
    {
        /**
         * @var GroupRole $service
         * @var User      $user
         * @var User      $group
         */
        [$service, $user, $group] = $params;

        $response = $service::getGroupSettingPermission($user, $group);

        $this->assertNotEmpty($response);

        foreach ($response as $permission => $flag) {
            $this->assertIsString($permission);
            $this->assertIsBool($flag);
        }
    }
}
