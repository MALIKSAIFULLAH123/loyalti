<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Policies;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\BackgroundStatus\Policies\Contracts\BgsCollectionPolicyInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewAnyPolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(BgsCollectionPolicy::class);
        $this->assertInstanceOf(BgsCollectionPolicyInterface::class, $policy);
    }

    public function testViewAnyPermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('viewAny', [BgsCollection::class]));
    }

    public function testViewAnyPermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('viewAny', [BgsCollection::class]));
    }

    public function testViewAnyPermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('viewAny', [BgsCollection::class]));
    }

    public function testViewAnyPermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertTrue($user->can('viewAny', [BgsCollection::class]));
    }

    public function testViewAnyPermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('viewAny', [BgsCollection::class]));
    }

    public function testViewAnyPermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('viewAny', [BgsCollection::class]));
    }
}
