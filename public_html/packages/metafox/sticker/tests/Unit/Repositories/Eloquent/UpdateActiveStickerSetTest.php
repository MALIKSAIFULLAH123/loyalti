<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class UpdateActiveStickerSetTest extends TestCase
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

        $this->actingAs($user);
        $stickerSet  = StickerSet::factory()->create();
        $checkActive = 0;

        $this->repository->toggleActive($user, $stickerSet->entityId(), 0);
        $this->assertTrue($checkActive == $stickerSet->refresh()->is_active);
    }
}
