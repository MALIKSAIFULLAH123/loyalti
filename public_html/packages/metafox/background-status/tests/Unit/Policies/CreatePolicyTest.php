<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Policies;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Policies\BgsCollectionPolicy;
use MetaFox\BackgroundStatus\Policies\Contracts\BgsCollectionPolicyInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CreatePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(BgsCollectionPolicy::class);
        $this->assertInstanceOf(BgsCollectionPolicyInterface::class, $policy);
    }

    public function testCreatePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('create', [BgsCollection::class]));
    }

    public function testCreatePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('create', [BgsCollection::class]));
    }

    public function testCreatePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('create', [BgsCollection::class]));
    }

    public function testCreatePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('create', [BgsCollection::class]));
    }

    public function testCreatePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('create', [BgsCollection::class]));
    }

    public function testCreatePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('create', [BgsCollection::class]));
    }
}
