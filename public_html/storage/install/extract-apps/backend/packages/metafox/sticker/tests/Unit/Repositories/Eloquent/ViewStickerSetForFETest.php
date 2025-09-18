<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Models\StickerUserValue;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class ViewStickerSetForFETest extends TestCase
{
    private StickerSetRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(StickerSetRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(StickerSetRepository::class, $this->repository);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $stickerSet1 = StickerSet::factory()->create();
        $stickerSet2 = StickerSet::factory()->create(['is_active' => 0]);
        $stickerSet3 = StickerSet::factory()->create(['is_deleted' => 1]);
        $stickerSet4 = StickerSet::factory()->create();

        Sticker::factory()->setStickerSetId($stickerSet1->entityId())->create();
        Sticker::factory()->setStickerSetId($stickerSet2->entityId())->create();
        Sticker::factory()->setStickerSetId($stickerSet3->entityId())->create();

        StickerUserValue::factory()->setUser($user)->setStickerSetId($stickerSet1->entityId())->create();
        StickerUserValue::factory()->setUser($user)->setStickerSetId($stickerSet2->entityId())->create();
        StickerUserValue::factory()->setUser($user)->setStickerSetId($stickerSet3->entityId())->create();
        StickerUserValue::factory()->setUser($user)->setStickerSetId($stickerSet4->entityId())->create();

        $params  = ['limit' => 10];
        $results = $this->repository->viewStickerSetsAll($user, $params);

        $this->assertCount(0, Arr::where($results->items(), fn ($item) => $item->is_deleted || !$item->is_active));
    }
}
