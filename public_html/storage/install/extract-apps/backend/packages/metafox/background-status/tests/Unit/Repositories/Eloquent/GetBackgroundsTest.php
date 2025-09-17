<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Models\Sticker;
use Tests\TestCase;

class GetBackgroundsTest extends TestCase
{
    private BgsCollectionRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(BgsCollectionRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(BgsCollectionRepository::class, $this->repository);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $bgsCollection = BgsCollection::factory()->create();
        BgsBackground::factory()->setCollectionId($bgsCollection->entityId())->create();
        BgsBackground::factory()->setCollectionId($bgsCollection->entityId())->create(['is_deleted' => Sticker::IS_DELETED]);

        $checkCount = 1;
        $params     = [
            'collection_id' => $bgsCollection->entityId(),
            'limit'         => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $backgrounds = $this->repository->getBackgrounds($user, $params);

        $this->assertTrue($checkCount == count($backgrounds->items()));
    }
}
