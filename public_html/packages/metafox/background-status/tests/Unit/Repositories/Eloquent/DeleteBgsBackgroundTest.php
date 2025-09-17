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

class DeleteBgsBackgroundTest extends TestCase
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

        $bgsCollection  = BgsCollection::factory()->create();
        $bgsBackground  = BgsBackground::factory()->setCollectionId($bgsCollection->entityId())->create();
        $bgsBackground2 = BgsBackground::factory()->setCollectionId($bgsCollection->entityId())->create();

        $checkCount = 1;
        $this->repository->deleteBackground($user, $bgsBackground->entityId());
        $this->assertTrue($checkCount == $bgsCollection->refresh()->total_background);
        $this->assertTrue($checkCount == $bgsCollection->backgrounds()->where('is_deleted', 0)->count());
        $this->assertTrue($bgsBackground2->entityId() == $bgsCollection->refresh()->main_background_id);
    }
}
