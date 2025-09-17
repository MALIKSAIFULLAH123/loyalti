<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class CreatePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }

    public function testCreatePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('create', [StickerSet::class]));
    }

    public function testCreatePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('create', [StickerSet::class]));
    }

    public function testCreatePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('create', [StickerSet::class]));
    }

    public function testCreatePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('create', [StickerSet::class]));
    }

    public function testCreatePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('create', [StickerSet::class]));
    }

    public function testCreatePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('create', [StickerSet::class]));
    }
}
