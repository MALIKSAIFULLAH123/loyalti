<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class OrderingStickerSetTest extends TestCase
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

        $stickerSet  = StickerSet::factory()->create();
        $stickerSet2 = StickerSet::factory()->create();

        $order1 = 1;
        $order2 = 2;

        $orders = [
            $stickerSet->entityId()  => $order2,
            $stickerSet2->entityId() => $order1,
        ];

        $this->repository->orderingStickerSet($user, $orders);
        $this->assertTrue($order2 == $stickerSet->refresh()->ordering);
        $this->assertTrue($order1 == $stickerSet2->refresh()->ordering);
    }
}
