<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Policies;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\BackgroundStatus\Policies\Contracts\BgsCollectionPolicyInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DeletePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(BgsCollectionPolicy::class);
        $this->assertInstanceOf(BgsCollectionPolicyInterface::class, $policy);
    }

    public function testDeletePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('delete', [BgsCollection::class]));
    }

    public function testDeletePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('delete', [BgsCollection::class]));
    }

    public function testDeletePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('delete', [BgsCollection::class]));
    }

    public function testDeletePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('delete', [BgsCollection::class]));
    }

    public function testDeletePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('delete', [BgsCollection::class]));
    }

    public function testDeletePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('delete', [BgsCollection::class]));
    }
}
