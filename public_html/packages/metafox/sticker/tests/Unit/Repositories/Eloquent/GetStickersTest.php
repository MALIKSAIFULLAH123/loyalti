<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class GetStickersTest extends TestCase
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
     * @throws ValidationException
     */
    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $stickerSet = StickerSet::factory()->create();
        Sticker::factory()->setStickerSetId($stickerSet->entityId())->create();
        Sticker::factory()->setStickerSetId($stickerSet->entityId())->create(['is_deleted' => Sticker::IS_DELETED]);

        $checkCount = 1;
        $params     = [
            'sticker_set_id' => $stickerSet->entityId(),
            'limit'          => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $stickers = $this->repository->getStickers($user, $params);

        $this->assertTrue($checkCount == count($stickers->items()));
    }
}
