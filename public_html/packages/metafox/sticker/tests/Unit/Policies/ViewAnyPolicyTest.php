<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class ViewAnyPolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }

    public function testViewAnyPermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('viewAny', [StickerSet::class]));
    }

    public function testViewAnyPermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('viewAny', [StickerSet::class]));
    }

    public function testViewAnyPermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('viewAny', [StickerSet::class]));
    }

    public function testViewAnyPermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertTrue($user->can('viewAny', [StickerSet::class]));
    }

    public function testViewAnyPermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('viewAny', [StickerSet::class]));
    }

    public function testViewAnyPermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('viewAny', [StickerSet::class]));
    }
}
