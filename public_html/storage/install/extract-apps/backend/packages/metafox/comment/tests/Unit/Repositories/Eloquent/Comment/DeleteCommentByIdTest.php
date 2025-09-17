<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;

class DeleteCommentByIdTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(CommentRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     *
     * @param CommentRepositoryInterface $repository
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testDeleteCommentById(CommentRepositoryInterface $repository): array
    {
        $user = $this->createNormalUser();
        $this->actingAs($user);
        $this->skipPolicies(CommentPolicy::class);
        $owner = $this->createNormalUser();

        $item    = $this->contentFactory()->setUser($owner)->setOwner($owner)->create();
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();
        CommentAttachment::factory()->create(['id' => $comment->entityId()]);
        $checkCount = 1;

        $this->assertTrue($checkCount == $item->refresh()->total_comment);
        $repository->deleteCommentById($user, $comment->entityId());
        $checkCount = 0;
        $this->assertTrue($checkCount == $item->refresh()->total_comment);

        return [$repository, $user, $owner];
    }

    /**
     * @depends testDeleteCommentById
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testDeleteChildSuccess(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var User                       $user2
         */
        [$repository, $user, $user2] = $data;

        $this->actingAs($user2);
        $item             = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $commentParent    = Comment::factory()->setUser($user)->setItem($item)->create();
        $commentChild     = Comment::factory()->setUser($user)->setItem($item)->create(['parent_id' => $commentParent->entityId()]);
        $checkCount       = 2;
        $checkParentCount = 1;

        $this->assertTrue($checkCount == $item->refresh()->total_comment);
        $this->assertTrue($checkParentCount == $commentParent->refresh()->total_comment);

        $repository->deleteCommentById($user, $commentChild->entityId());
        $checkCount = 0;
        $this->assertTrue($checkCount == $commentParent->refresh()->total_comment);
        $checkCount = 1;
        $this->assertTrue($checkCount == $item->refresh()->total_comment);

        return [$repository, $user, $user2];
    }

    /**
     * @depends testDeleteCommentById
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testDeleteParentSuccess(array $data)
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var User                       $user2
         */
        [$repository, $user, $user2] = $data;

        $this->actingAs($user2);
        $item             = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $commentParent    = Comment::factory()->setUser($user)->setItem($item)->create();
        $commentChild     = Comment::factory()->setUser($user)->setItem($item)->create(['parent_id' => $commentParent->entityId()]);
        $checkCount       = 2;
        $checkParentCount = 1;

        $this->assertTrue($checkCount == $item->refresh()->total_comment);
        $this->assertTrue($checkParentCount == $commentParent->refresh()->total_comment);

        $this->actingAs($user);
        $repository->deleteCommentById($user, $commentParent->entityId());
        $checkCount = 0;
        $this->assertTrue($checkCount == $item->refresh()->total_comment);

        $this->assertEmpty(Comment::query()->find($commentChild->entityId()));
    }
}
