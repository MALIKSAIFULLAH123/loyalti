<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Carbon\Carbon;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Friend\Models\Friend;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GetRelatedCommentTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(CommentRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);

        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user4 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        return [$user, $user2, $user3, $user4, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $params
     *
     * @return array<int, mixed>
     */
    public function testSuccess(array $params): array
    {
        /**
         * @var User                       $user
         * @var User                       $user2
         * @var User                       $user3
         * @var User                       $user4
         * @var CommentRepositoryInterface $repository
         */
        [$user, $user2, $user3, $user4, $repository] = $params;
        $this->skipPolicies(CommentPolicy::class);
        $this->actingAs($user2);
        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();

        $this->actingAs($user);
        Comment::factory()->setUser($user)->setItem($item)->create();
        $this->actingAs($user3);
        Comment::factory()->setUser($user3)->setItem($item)->create();
        $this->actingAs($user4);
        Comment::factory()->setUser($user4)->setItem($item)->create();

        $this->actingAs($user);
        Friend::factory()->setUser($user)->setOwner($user3)->create();
        $this->actingAs($user3);
        Friend::factory()->setUser($user3)->setOwner($user)->create();

        $this->mockSiteSettings([
            'comment.prefetch_comments_on_feed' => 5,
        ]);

        $this->actingAs($user);

        $result = $repository->getRelatedComments($user, $item);

        $this->assertCount(3, $result);

        $this->assertTrue($result->contains('user_id', $user3->entityId()));

        Friend::query()->where([
            ['user_id', '=', $user->entityId()],
            ['owner_id', '=', $user3->entityId()],
        ])->orWhere([
            ['user_id', '=', $user3->entityId()],
            ['owner_id', '=', $user->entityId()],
        ])->delete();

        return [$user, $user2, $user3, $user4, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $params
     */
    public function testCommentChildSuccess(array $params): array
    {
        /**
         * @var User                       $user
         * @var User                       $user2
         * @var User                       $user3
         * @var User                       $user4
         * @var CommentRepositoryInterface $repository
         */
        [$user, $user2, $user3, $user4, $repository] = $params;

        $this->actingAs($user2);
        $item    = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();
        Comment::factory()->setUser($user3)->setItem($item)->create(['parent_id' => $comment->entityId()]);
        Comment::factory()->setUser($user4)->setItem($item)->create(['parent_id' => $comment->entityId()]);
        Comment::factory()->setUser($user4)->setItem($item)->create();

        Friend::factory()->setUser($user)->setOwner($user3)->create();
        Friend::factory()->setUser($user3)->setOwner($user)->create();

        $this->mockSiteSettings([
            'comment.prefetch_comments_on_feed' => 5,
        ]);
        $this->actingAs($user);

        $result = $repository->getRelatedComments($user, $item);
        $this->assertInArray($user->entityId(), $result->pluck('user_id')->toArray());
        $this->assertFalse($result->first()->children->contains('user_id', '=', $user3->entityId()));

        $commentChildrenIds = $checkSort = $result->first()->children->pluck('id')->toArray();
        rsort($checkSort);
        $this->assertEquals($checkSort, $commentChildrenIds);

        Friend::query()->where([
            ['user_id', '=', $user->entityId()],
            ['owner_id', '=', $user3->entityId()],
        ])->orWhere([
            ['user_id', '=', $user3->entityId()],
            ['owner_id', '=', $user->entityId()],
        ])->delete();

        return [$user, $user2, $user3, $user4, $repository];
    }

    /**
     * @depends testCommentChildSuccess
     *
     * @param array<int, mixed> $params
     */
    public function testNotFriendSuccess(array $params)
    {
        /**
         * @var User                       $user
         * @var User                       $user2
         * @var User                       $user3
         * @var User                       $user4
         * @var CommentRepositoryInterface $repository
         */
        [$user, $user2, $user3, $user4, $repository] = $params;

        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user2);
        Comment::factory()->setUser($user)->setItem($item)->create();
        $comment2 = Comment::factory()->setUser($user3)->setItem($item)->create();
        $this->actingAs($user);
        /** @var Carbon $comment2Time */
        $comment2Time = $comment2->updated_at;
        Comment::factory()->setUser($user4)->setItem($item)->create([
            'created_at' => $comment2Time->addMinute(),
            'updated_at' => $comment2Time->addMinute(),
        ]);

        $result = $repository->getRelatedComments($user, $item);

        $this->assertInArray($user4->entityId(), $result->pluck('user_id')->toArray());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $params
     */
    public function testSelfSuccess(array $params)
    {
        /**
         * @var User                       $user
         * @var User                       $user2
         * @var User                       $user3
         * @var User                       $user4
         * @var CommentRepositoryInterface $repository
         */
        [$user, $user2, , , $repository] = $params;

        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user2);
        Comment::factory()->setUser($user)->setItem($item)->create();

        $result = $repository->getRelatedComments($user, $item);

        $this->assertInArray($user->entityId(), $result->pluck('user_id')->toArray());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $params
     */
    public function testNotFoundCommentSuccess(array $params)
    {
        /**
         * @var User                       $user
         * @var User                       $user2
         * @var User                       $user3
         * @var User                       $user4
         * @var CommentRepositoryInterface $repository
         */
        [$user, $user2, , , $repository] = $params;

        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);
        $result = $repository->getRelatedComments($user, $item);
        $this->assertSame(0, $result->count());
    }
}
