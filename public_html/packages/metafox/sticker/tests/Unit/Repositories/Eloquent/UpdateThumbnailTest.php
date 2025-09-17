<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class UpdateThumbnailTest extends TestCase
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

    public function testSuccess()
    {
        $stickerSet = StickerSet::factory()->create();
        $sticker1 = Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();
        $sticker2 = Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();

        $this->repository->updateThumbnail($stickerSet);
        $this->assertTrue($sticker1->entityId() == $stickerSet->refresh()->thumbnail_id);

        $this->repository->updateThumbnail($stickerSet, $sticker2->entityId());
        $this->assertTrue($sticker2->entityId() == $stickerSet->refresh()->thumbnail_id);
    }
}
