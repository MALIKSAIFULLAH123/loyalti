<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Models\StickerUserValue;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class DeleteUserStickerSetTest extends TestCase
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
        StickerUserValue::factory()->setUser($user)->setStickerSetId($stickerSet->entityId())->create();

        $this->repository->deleteUserStickerSet($user, $stickerSet->entityId());

        $params = [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'set_id'    => $stickerSet->entityId(),
        ];

        $checkExist = StickerUserValue::query()
            ->where($params)
            ->exists();

        $this->assertFalse($checkExist);
    }
}
