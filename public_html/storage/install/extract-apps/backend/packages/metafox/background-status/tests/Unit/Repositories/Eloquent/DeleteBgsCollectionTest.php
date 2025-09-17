<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DeleteBgsCollectionTest extends TestCase
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

        $this->repository->deleteBgsCollection($user, $bgsCollection->entityId());
        $bgsCollection->refresh();
        $checkCount = 1;

        $this->assertTrue(BgsCollection::IS_DELETED == $bgsCollection->is_deleted);
        $this->assertTrue($checkCount == $bgsCollection->total_background);

        $backgroundDeleted = $bgsCollection->backgrounds()->where('is_deleted', BgsBackground::IS_DELETED)->count();
        $this->assertSame($checkCount, $backgroundDeleted);
    }
}
