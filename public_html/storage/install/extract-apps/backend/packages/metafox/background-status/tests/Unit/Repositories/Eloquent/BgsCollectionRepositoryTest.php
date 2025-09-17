<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BgsCollectionRepositoryTest extends TestCase
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

        $this->repository->getModel()->newQuery()->delete();

        $user = $this->asAdminUser();

        /** @var BgsCollection[] $createdItems */
        $createdItems = BgsCollection::factory()
            ->times(4)
            ->sequence(
                ['is_active' => 1],
                ['is_active' => 1, 'is_deleted' => 1],
                ['is_active' => 0, 'is_deleted' => 1],
                []
            )
            ->create();

        $this->assertCount(4, $createdItems);

        return $user;
    }

    /**
     * @throws AuthorizationException
     * @depends testInstance
     * @testdox admincp does not load is_deleted but is_active=false, and true
     */
    public function test_viewBgsCollectionsForAdmin($user)
    {
        $params  = ['limit' => 10];
        $results = $this->repository->viewBgsCollectionsForAdmin($user, $params);

        $this->assertCount(0, Arr::where($results->items(), fn ($x) => $x->is_deleted));
    }

    /**
     * @throws AuthorizationException
     * @depends testInstance
     */
    public function test_viewBgsCollectionsForFE($user)
    {
        $params  = ['limit' => 100];
        $results = $this->repository->viewBgsCollectionsForFE($user, $params);

        $this->assertCount(0, Arr::where($results->items(), fn ($x) => $x->is_deleted || !$x->is_active));
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @depends testInstance
     */
    public function test_viewBgsCollection()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $bgsCollection = BgsCollection::query()->first();

        /** @var Collection $items */
        $items = BgsBackground::factory()
            ->times(2)
            ->sequence(
                ['is_deleted' => 1],
                []
            )->setCollectionId($bgsCollection->getKey())->create();

        $this->assertSame(2, $items->count());

        $checkCount    = 1;
        $bgsCollection = $this->repository->viewBgsCollection($user, $bgsCollection->getKey());

        $this->assertNotEmpty($bgsCollection);
        $this->assertCount($checkCount, $bgsCollection->backgrounds);
    }
}

// end
