<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewCommentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipPolicies(CommentPolicy::class);
    }

    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(CommentRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);

        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        return [$repository, $user, $user2];
    }

    /**
     * @depends testInstance
     *
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var User                       $user2
         */
        [$repository, $user, $user2] = $data;
        $this->actingAs($user);
        $item = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        Comment::factory()->setUser($user)->setItem($item)->create();

        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'parent_id' => 0,
            'excludes'  => [],
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $checkCount = 1;
        $results    = $repository->viewComments($user, $params);

        $this->assertCount($checkCount, $results);

        return [$repository, $user, $item];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testParentCommentSuccess(array $data)
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var User                       $user2
         */
        [$repository, $user, $user2] = $data;
        $this->actingAs($user2);
        $item    = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();
        Comment::factory()->setUser($user)->setItem($item)->create(['parent_id' => $comment->entityId()]);

        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'parent_id' => $comment->entityId(),
            'excludes'  => [],
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $checkCount = 1;
        $results    = $repository->viewComments($user, $params);

        $this->assertCount($checkCount, $results);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testItemNotFound(array $data)
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var User                       $user2
         */
        [$repository, $user] = $data;
        $params              = [
            'item_id'   => 0,
            'item_type' => ContentModel::ENTITY_TYPE,
            'parent_id' => 0,
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];
        $this->expectException(ModelNotFoundException::class);
        $repository->viewComments($user, $params);
    }

    /**
     * @depends testSuccess
     * @return void
     * @throws AuthorizationException
     */
    public function testViewCommentsWithExcludes(array $data)
    {
        /** @var CommentRepository $repository */
        [$repository, $user, $item] = $data;
        $this->actingAs($user);
        Comment::factory()->setItem($item)->setUser($user)->count(10)->create();
        $excludesComments = Comment::query()->orderByDesc('created_at')->limit(5)->get();
        $excludes         = $excludesComments->pluck('id')->toArray();
        $params           = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'parent_id' => 0,
            'excludes'  => $excludes,
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $result = $repository->viewComments($user, $params);
        $this->assertTrue($result->count() > 0);

        $this->assertEmpty(array_intersect($result->pluck('id')->toArray(), $excludes));
    }
}
