<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class OrderingStickerTest extends TestCase
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

        $stickerSet = StickerSet::factory()->create();
        $sticker    = Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();
        $sticker2   = Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();

        $order1 = 1;
        $order2 = 2;

        $orders = [
            $sticker->entityId()  => $order2,
            $sticker2->entityId() => $order1,
        ];

        $this->repository->orderingSticker($user, $orders);
        $this->assertTrue($order2 == $sticker->refresh()->ordering);
        $this->assertTrue($order1 == $sticker2->refresh()->ordering);
    }
}
