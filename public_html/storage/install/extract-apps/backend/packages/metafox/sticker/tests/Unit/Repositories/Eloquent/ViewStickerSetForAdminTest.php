<?php

namespace MetaFox\Sticker\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\Eloquent\StickerSetRepository;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;
use Tests\TestCase;

class ViewStickerSetForAdminTest extends TestCase
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

        StickerSet::factory()->create();
        StickerSet::factory()->create(['is_active' => 0]);
        StickerSet::factory()->create(['is_deleted' => 1]);

        $params  = ['limit' => 10];
        $results = $this->repository->viewStickerSetsForAdmin($user, $params);

        $this->assertEmpty(Arr::where($results->items(), fn ($item) => $item->is_deleted));
    }
}
