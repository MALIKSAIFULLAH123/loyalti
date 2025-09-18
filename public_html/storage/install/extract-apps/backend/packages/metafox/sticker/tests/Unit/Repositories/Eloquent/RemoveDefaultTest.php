<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class RemoveDefaultTest extends TestCase
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

        $stickerSet   = StickerSet::factory()->create(['is_default' => 1]);
        $checkDefault = 0;

        $this->repository->removeDefault($user, $stickerSet->entityId());
        $this->assertTrue($checkDefault == $stickerSet->refresh()->is_default);
    }
}
