<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use Tests\TestCase;

class MarkAsDefaultTest extends TestCase
{
    private StickerSetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(StickerSetRepository::class);
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

        $this->repository->getModel()->newQuery()->where(['is_default' => 1])->update(['is_default' => 0]);

        $stickerSet  = StickerSet::factory()->create();
        $stickerSet2 = StickerSet::factory()->create();
        $stickerSet3 = StickerSet::factory()->create();

        $this->expectException(ValidationException::class);

        $this->repository->markAsDefault($user, $stickerSet->entityId());
        $this->repository->markAsDefault($user, $stickerSet2->entityId());
        $this->repository->markAsDefault($user, $stickerSet3->entityId());
    }
}
