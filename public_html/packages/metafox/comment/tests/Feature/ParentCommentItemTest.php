<?php

namespace MetaFox\Comment\Tests\Feature;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ParentCommentItemTest extends TestCase
{
    public function testCreateInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(HasTotalComment::class, $item);
        $this->assertInstanceOf(HasTotalCommentWithReply::class, $item);
        $this->assertSame(0, $item->total_comment);
        $this->assertSame(0, $item->total_reply);

        return [$user, $item];
    }

    /**
     * @depends testCreateInstance
     *
     * @param array<mixed> $params
     */
    public function testCommentItem(array $params): array
    {
        /**
         * @var User    $user
         * @var Content $item
         */
        [$user, $item] = $params;

        $comment = Comment::factory()->setUser($user)->setItem($item)->create();

        $item->refresh();

        $this->assertSame(1, $item->total_comment);

        return [$user, $item, $comment];
    }

    /**
     * @depends testCommentItem
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function testCommentOnParentComment(array $params): array
    {
        /**
         * @var User                             $user
         * @var Content|HasTotalCommentWithReply $item
         * @var Comment|HasTotalComment          $comment
         */
        [$user, $item, $comment] = $params;

        $childComment = Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $comment->entityId(),
        ]);

        $item->refresh();
        $comment->refresh();

        $this->assertSame(2, $item->total_comment);
        $this->assertSame(1, $item->total_reply);
        $this->assertSame(1, $comment->total_comment);

        return [$user, $item, $comment, $childComment];
    }

    /**
     * @depends testCommentOnParentComment
     *
     * @param array<mixed> $params
     */
    public function testDeleteCommentOnParentComment(array $params)
    {
        /**
         * @var User                             $user
         * @var Content|HasTotalCommentWithReply $item
         * @var Comment|HasTotalComment          $comment
         * @var Comment|HasTotalComment          $childComment
         */
        [$user, $item, $comment, $childComment] = $params;

        $childComment->delete();

        $item->refresh();
        $comment->refresh();

        $this->assertSame(1, $item->total_comment);
        $this->assertSame(0, $item->total_reply);
        $this->assertSame(0, $comment->total_comment);

        return [$user, $item, $comment];
    }

    /**
     * @depends testDeleteCommentOnParentComment
     *
     * @param array<mixed> $params
     */
    public function testCommentOnParentCommentMultiple(array $params)
    {
        /**
         * @var User                             $user
         * @var Content|HasTotalCommentWithReply $item
         * @var Comment|HasTotalComment          $comment
         */
        [$user, $item, $comment] = $params;

        Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $comment->entityId(),
        ]);
        Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $comment->entityId(),
        ]);
        Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $comment->entityId(),
        ]);

        $item->refresh();
        $comment->refresh();

        $this->assertSame(4, $item->total_comment); // 1 root comment with 3 replies.
        $this->assertSame(3, $item->total_reply);
        $this->assertSame(3, $comment->total_comment); // Root comment has 3 replies.

        // Add 1 root comment + 2 replies.
        $rootComment2 = Comment::factory()->setUser($user)->setItem($item)->create([]);
        Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $rootComment2->entityId(),
        ]);
        Comment::factory()->setUser($user)->setItem($item)->create([
            'parent_id' => $rootComment2->entityId(),
        ]);

        $item->refresh();
        $comment->refresh();
        $rootComment2->refresh();

        $this->assertSame(4 + 3, $item->total_comment); // 1 root comment with 3 replies.
        $this->assertSame(3 + 2, $item->total_reply);
        $this->assertSame(3, $comment->total_comment);      // Root comment has 3 replies.
        $this->assertSame(2, $rootComment2->total_comment); // Root comment 2 has 2 replies.
    }
}
