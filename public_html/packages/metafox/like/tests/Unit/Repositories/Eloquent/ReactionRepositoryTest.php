<?php

namespace MetaFox\Like\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Testing\File;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Repositories\Eloquent\ReactionRepository;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class ReactionRepositoryTest extends TestCase
{
    private ReactionRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(ReactionRepository::class);
    }

    public function testCreateStaffUser()
    {
        $user = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $this->expectNotToPerformAssertions();

        return $user;
    }

    /**
     * @depends testCreateStaffUser
     * @depends testCreateReaction
     * @throws AuthorizationException
     */
    public function test_viewReactionsForFE($staff, $reaction)
    {
        $results    = $this->repository->viewReactionsForFE($staff);
        $checkCount = 0;
        $this->assertTrue($checkCount < $results->count());
    }

    /**
     * @throws AuthorizationException
     * @depends testCreateStaffUser
     * @depends testCreateReaction
     */
    public function test_viewReactionsForAdmin($staff, $reaction)
    {
        $results    = $this->repository->viewReactionsForAdmin($staff, ['limit' => Pagination::DEFAULT_ITEM_PER_PAGE]);
        $checkCount = 0;
        $this->assertTrue($checkCount < count($results->items()));
    }

    /**
     * @depends testCreateStaffUser
     * @depends testCreateReaction
     * @throws AuthorizationException
     */
    public function test_viewReaction($staff, $reaction)
    {
        $result = $this->repository->viewReaction($staff, $reaction->entityId());
        $this->assertTrue($result->entityId() == $reaction->entityId());
    }
}

// end
