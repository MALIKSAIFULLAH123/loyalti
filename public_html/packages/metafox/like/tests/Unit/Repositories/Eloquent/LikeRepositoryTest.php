<?php

namespace MetaFox\Like\Tests\Unit\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Like\Repositories\Eloquent\LikeRepository;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeRepositoryTest extends TestCase
{
    private LikeRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(LikeRepository::class);
    }

    public function testCreateUser()
    {
        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        $item       = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $totalLike  = $item->total_like;
        $checkCount = 1;
        $this->actingAs($user);
        //add like-> item total_like = 1, $likeAgg1 = 1
        $this->repository->createLike($user, $item->entityId(), $item->entityType(), 1);
        $item->refresh();
        $this->assertTrue($totalLike == ($item->refresh()->total_like - $checkCount));

        /** @var LikeAgg $likeAgg1 */
        $likeAgg1 = LikeAgg::query()->where([
            'item_id'     => $item->entityId(),
            'item_type'   => $item->entityType(),
            'reaction_id' => 1,
        ])->first();

        $this->assertNotEmpty($likeAgg1);
        $this->assertTrue($likeAgg1->total_reaction == $checkCount);

        //add like-> item total_like = 2, $likeAgg1 = 2
        $user3      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $checkCount = 2;
        $this->actingAs($user3);
        $this->repository->createLike($user3, $item->entityId(), $item->entityType(), 1);
        $item->refresh();
        $this->assertTrue($totalLike == ($item->refresh()->total_like - $checkCount));

        //add like-> item total_like = 2, $likeAgg1 = 1, $likeAgg2 = 1
        $this->actingAs($user3);
        $this->repository->createLike($user3, $item->entityId(), $item->entityType(), 2);
        $item->refresh();
        $this->assertTrue($totalLike == ($item->refresh()->total_like - $checkCount));

        /** @var LikeAgg $likeAgg2 */
        $likeAgg2 = LikeAgg::query()->where([
            'item_id'     => $item->entityId(),
            'item_type'   => $item->entityType(),
            'reaction_id' => 2,
        ])->first();

        $checkCount2 = 1;
        $this->assertNotEmpty($likeAgg2);
        $this->assertTrue($likeAgg2->total_reaction == $checkCount2);

        $this->assertTrue($likeAgg1->refresh()->total_reaction == $checkCount2);

        //add like-> item total_like = 2, $likeAgg1 = 2, $likeAgg2 = 0
        $this->actingAs($user3);
        $this->repository->createLike($user3, $item->entityId(), $item->entityType(), 1);
        $checkCount3 = 0;

        $this->assertTrue($likeAgg1->refresh()->total_reaction == $checkCount);
        $this->assertTrue($likeAgg2->refresh()->total_reaction == $checkCount3);

        return $user;
    }

    /**
     * @depends testCreateUser
     * @throws AuthorizationException
     */
    public function testItemNotFound($user)
    {
        $this->actingAs($user);
        $this->expectException(\Throwable::class);
        $this->repository->createLike($user, 0, 'test', 0);
    }

    /**
     * @depends testCreateUser
     * @throws AuthorizationException
     */
    public function test_deleteLikeById($user)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();

        $this->actingAs($user);
        $like = Like::factory()->setUser($user)->setItem($item)->create();

        $totalLike = $item->refresh()->total_like;

        $checkCount = 1;
        $this->actingAs($user);
        $this->repository->deleteLikeById($user, $like->entityId());
        $this->assertTrue($item->refresh()->total_like == ($totalLike - $checkCount));
    }

    /**
     * @depends testCreateUser
     */
    public function testDeleteLikeByUserAndItemTest($user)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);
        Like::factory()->setUser($user)->setItem($item)->create();

        $totalLike = $item->refresh()->total_like;

        $checkCount = 1;
        $this->repository->deleteByUserAndItem($user, $item->entityId(), $item->entityType());
        $this->assertTrue($item->refresh()->total_like == ($totalLike - $checkCount));
    }

    /**
     * @depends testCreateUser
     */
    public function testDeleteLikeByUserTest($user)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item  = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $item2 = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();

        $this->actingAs($user);
        Like::factory()->setUser($user)->setItem($item)->create();
        Like::factory()->setUser($user)->setItem($item2)->create();

        $this->repository->deleteByUser($user);

        $likes = $this->repository->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->get();

        $this->assertEmpty($likes);
    }

    /**
     * @depends testCreateUser
     * @throws AuthorizationException
     */
    public function testViewLikesTest($user)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item  = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();

        $this->actingAs($user);
        Like::factory()->setUser($user)->setItem($item)->create(['reaction_id' => 1]);

        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'react_id'  => 1,
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $checkCount = 1;
        $results    = $this->repository->viewLikes($user, $params);
        $this->assertCount($checkCount, $results->items());
    }

    /**
     * @depends testCreateUser
     * @throws AuthorizationException
     */
    public function testFindItemNotFound($user)
    {
        $params = [
            'item_id'   => 0,
            'item_type' => 'test',
            'react_id'  => 1,
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $this->actingAs($user);
        $this->expectException(ModelNotFoundException::class);
        $this->repository->viewLikes($user, $params);
    }

    /**
     * @depends testCreateUser
     * @throws AuthorizationException
     */
    public function testViewLikeTabsTest($user)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user2);
        $item  = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();

        $this->actingAs($user);
        Like::factory()->setUser($user)->setItem($item)->create(['reaction_id' => 1]);
        $this->actingAs($user3);
        Like::factory()->setUser($user3)->setItem($item)->create(['reaction_id' => 2]);

        $checkCount = 3;
        $this->actingAs($user);
        $results    = $this->repository->viewLikeTabs($user, $item->entityId(), $item->entityType());
        $this->assertTrue($checkCount == count($results));
    }

    /**
     * @throws AuthorizationException
     * @depends testCreateUser
     */
    public function testViewLikeTabsItemNotFound($user)
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($user);
        $this->repository->viewLikeTabs($user, 0, 'test');
    }
}

// end
