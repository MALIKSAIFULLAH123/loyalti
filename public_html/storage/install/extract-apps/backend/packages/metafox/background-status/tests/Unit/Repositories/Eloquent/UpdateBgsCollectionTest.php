<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateBgsCollectionTest extends TestCase
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
        $this->markTestIncomplete();

        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $tempFile      = $this->createTempFile($user, 'test.jpg', 'background-status');
        $bgsCollection = BgsCollection::factory()->create(['is_default' => 0]);
        $title         = $this->faker->title;

        $params = [
            'title'                => $title,
            'background_temp_file' => [$tempFile->id],
            'is_default'           => BgsCollection::IS_DEFAULT,
        ];

        $bgsCollection = $this->repository->updateBgsCollection($user, $bgsCollection->entityId(), $params);
        $checkCount    = 1;
        $this->assertNotEmpty($bgsCollection);
        $this->assertTrue($checkCount == $bgsCollection->total_background);
        $this->assertTrue($title == $bgsCollection->title);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testUpdateAlreadyDefaultSuccess()
    {
        $this->markTestSkipped();

        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $bgsCollection = BgsCollection::factory()->create(['is_default' => BgsCollection::IS_DEFAULT]);

        $bgsCollection->refresh();

        $this->assertEquals(BgsCollection::IS_DEFAULT, $bgsCollection->is_default);

        $params = [
            'is_default' => 0,
        ];

        $bgsCollection = $this->repository->updateBgsCollection($user, $bgsCollection->entityId(), $params);

        // @todo packages/metafox/background-status/tests/Unit/Repositories/Eloquent/UpdateBgsCollectionTest.php:73
        $this->assertEquals(BgsCollection::IS_DEFAULT, $bgsCollection->is_default);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testUpdateViewOnlySuccess()
    {
        $user          = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $bgsCollection = BgsCollection::factory()->create(['view_only' => BgsCollection::IS_VIEW_ONLY]);

        $this->expectException(ValidationException::class);
        $this->repository->updateBgsCollection($user, $bgsCollection->entityId(), []);
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testUpdateIsDeletedSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);

        $bgsCollection = BgsCollection::factory()->create(['is_deleted' => BgsCollection::IS_DELETED]);

        $this->expectException(ValidationException::class);
        $this->repository->updateBgsCollection($user, $bgsCollection->entityId(), []);
    }
}
