<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Policies;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\BackgroundStatus\Policies\Contracts\BgsCollectionPolicyInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdatePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(BgsCollectionPolicy::class);
        $this->assertInstanceOf(BgsCollectionPolicyInterface::class, $policy);
    }

    public function testUpdatePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('update', [BgsCollection::class]));
    }

    public function testUpdatePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('update', [BgsCollection::class]));
    }

    public function testUpdatePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('update', [BgsCollection::class]));
    }

    public function testUpdatePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('update', [BgsCollection::class]));
    }

    public function testUpdatePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('update', [BgsCollection::class]));
    }

    public function testUpdatePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('update', [BgsCollection::class]));
    }
}
