<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\BackgroundStatus\Repositories\Eloquent\BgsCollectionRepository;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class CreateBgsCollectionTest extends TestCase
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
     * @throws AuthorizationException|ValidatorException
     */
    public function testSuccess()
    {
        $this->markTestIncomplete();

        $user     = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $tempFile = $this->createTempFile($user, 'test.jpg', 'background-status');

        $params = [
            'title'                => $this->faker->title,
            'background_temp_file' => [$tempFile->id],
            'view_only'            => 1,
        ];

        $bgsCollection = $this->repository->createBgsCollection($user, $params);
        $checkCount    = 1;
        $this->assertNotEmpty($bgsCollection);
        $this->assertTrue($checkCount == $bgsCollection->total_background);
    }
}
