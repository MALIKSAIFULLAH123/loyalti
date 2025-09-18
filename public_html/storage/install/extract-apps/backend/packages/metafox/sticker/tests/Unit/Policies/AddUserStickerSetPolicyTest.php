<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class AddUserStickerSetPolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }

    public function testAddUserStickerSetPermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('addUserStickerSet', [StickerSet::class]));
    }

    public function testAddUserStickerSetPermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('addUserStickerSet', [StickerSet::class]));
    }

    public function testAddUserStickerSetPermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('addUserStickerSet', [StickerSet::class]));
    }

    public function testAddUserStickerSetPermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertTrue($user->can('addUserStickerSet', [StickerSet::class]));
    }

    public function testAddUserStickerSetPermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('addUserStickerSet', [StickerSet::class]));
    }

    public function testAddUserStickerSetPermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('addUserStickerSet', [StickerSet::class]));
    }
}
