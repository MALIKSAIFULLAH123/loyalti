<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    private CommentRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(CommentRepository::class);
    }

    /**
     * @return \MetaFox\Blog\Models\Blog
     */
    public function testMakeAnItem()
    {
        $user  = $this->createNormalUser();
        $this->actingAs($user);

        $item = $this->contentFactory()
            ->setUser($user)
            ->setOwner($user)
            ->create(['privacy' => 0]);

        $this->assertEquals(0, $item->total_comment);
        $this->assertEquals(0, $item->total_reply);

        return $item;
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @depends  testMakeAnItem
     */
    public function testCreateCommentSuccess($item)
    {
        $user = $item->user;

        $this->actingAs($user);

        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'text'      => $this->faker->text(),
            'photo_id'  => 0,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $comment = $this->repository->createComment($user, $params);

        $this->assertInstanceOf(Comment::class, $comment);
        $comment->refresh();
        $item = $item->refresh();

        // increase total comment.
        $this->assertEquals(1, $item->total_comment);
        $this->assertEquals(0, $item->total_reply);

        return $comment;
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @depends  testCreateCommentSuccess
     */
    public function testCreateReplySuccess(Comment $comment)
    {
        $user = $comment->user;
        $item = $comment->item;

        $this->actingAs($user);

        $params = [
            'item_id'   => $comment->itemId(),
            'item_type' => $comment->itemType(),
            'text'      => $this->faker->text(),
            'photo_id'  => 0,
            'parent_id' => $comment->id,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $reply = $this->repository->createComment($user, $params);

        $item->refresh();
        $reply->refresh();

        $this->assertEquals(2, $item->total_comment);
        $this->assertEquals(1, $item->total_reply);

        return $reply;
    }

    public function testCreateCommentButItemNotFound()
    {
        $user = $this->createNormalUser();
        $this->actingAs($user);

        $params = [
            'text'      => 'a',
            'item_id'   => 0,
            'item_type' => 'blog',
            'photo_id'  => 0,
        ];

        $this->skipPolicies(CommentPolicy::class);
        $this->expectException(\Throwable::class);
        $this->repository->createComment($user, $params);
    }

    /**
     * @throws AuthorizationException
     * @depends testCreateCommentSuccess
     */
    public function testDeleteCommentById($comment)
    {
        $user = $comment->user;
        $item = $comment->item;

        $this->actingAs($user);

        $this->repository->deleteCommentById($user, $comment->entityId());

        $item->refresh();
        $this->assertSame(0, $item->total_comment);
    }
}
