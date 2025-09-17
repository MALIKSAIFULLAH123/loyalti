<?php

namespace MetaFox\Sticker\Tests\Unit\Policies;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Policies\Contracts\StickerSetPolicyInterface;
use MetaFox\Sticker\Policies\StickerSetPolicy;
use Tests\TestCase;

class DeletePolicyTest extends TestCase
{
    public function testInstance()
    {
        $policy = resolve(StickerSetPolicy::class);
        $this->assertInstanceOf(StickerSetPolicyInterface::class, $policy);
    }

    public function testDeletePermissionSuperAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::SUPER_ADMIN_USER);
        $this->assertTrue($user->can('delete', [StickerSet::class]));
    }

    public function testDeletePermissionAdminUser()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->assertTrue($user->can('delete', [StickerSet::class]));
    }

    public function testDeletePermissionStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->assertTrue($user->can('delete', [StickerSet::class]));
    }

    public function testDeletePermissionRegisteredUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertFalse($user->can('delete', [StickerSet::class]));
    }

    public function testDeletePermissionGuestUser()
    {
        $user = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $this->assertFalse($user->can('delete', [StickerSet::class]));
    }

    public function testDeletePermissionBannedUser()
    {
        $user = $this->createUser()->assignRole(UserRole::BANNED_USER);
        $this->assertFalse($user->can('delete', [StickerSet::class]));
    }
}
