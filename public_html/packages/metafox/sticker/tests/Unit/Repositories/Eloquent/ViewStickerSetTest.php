<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class ViewStickerSetTest extends TestCase
{
    public function testCreateInstance(): StickerSetRepositoryInterface
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
        Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();
        Sticker::factory()->setStickerSetId($stickerSet->entityId())->create(['is_deleted' => Sticker::IS_DELETED]);

        $checkCount = 1;
        $stickerSet = $service->viewStickerSet($user, $stickerSet->entityId());
        $this->assertNotEmpty($stickerSet);
        $this->assertTrue($checkCount == $stickerSet->stickers->count());
    }
}
