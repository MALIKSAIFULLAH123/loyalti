<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class UpdatePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }

    public function testUpdatePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('update', [StickerSet::class]));
    }

    public function testUpdatePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('update', [StickerSet::class]));
    }

    public function testUpdatePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('update', [StickerSet::class]));
    }

    public function testUpdatePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('update', [StickerSet::class]));
    }

    public function testUpdatePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('update', [StickerSet::class]));
    }

    public function testUpdatePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('update', [StickerSet::class]));
    }
}
