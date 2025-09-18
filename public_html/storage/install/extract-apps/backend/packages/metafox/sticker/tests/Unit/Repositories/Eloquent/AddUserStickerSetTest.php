<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class AddUserStickerSetTest extends TestCase
{
    /**
     * @return StickerSetRepositoryInterface
     */
    public function testCreateInstance()
    {
        $service = resolve(StickerSetRepositoryInterface::class);
        $this->assertInstanceOf(StickerSetRepository::class, $service);

        return $service;
    }

    /**
     * @depends testCreateInstance
     *
     * @param StickerSetRepositoryInterface $service
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testSuccess(StickerSetRepositoryInterface $service)
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $stickerSet = StickerSet::factory()->create();

        $result = $service->addUserStickerSet($user, $stickerSet->entityId());
        $this->assertTrue($result);

        $this->expectException(ValidationException::class);
        $service->addUserStickerSet($user, $stickerSet->entityId());
    }
}
