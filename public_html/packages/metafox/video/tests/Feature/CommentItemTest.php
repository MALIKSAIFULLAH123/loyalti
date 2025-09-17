<?php

namespace MetaFox\Video\Tests\Feature;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Video;
use Tests\TestCase;

class CommentItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = Video::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(HasTotalComment::class, $item);
        $this->assertSame(0, $item->total_comment);

        return [$user, $user2, $item];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>        $params
     * @return array<int,        mixed>
     */
    public function testCommentItem(array $params): array
    {
        [, $user2, $item] = $params;

        $comment = Comment::factory()
            ->setUser($user2)
            ->setOwner($user2)
            ->setItem($item)
            ->create();

        $item->refresh();

        $this->assertSame(1, $item->total_comment);

        return [$item, $comment];
    }

    /**
     * @depends testCommentItem
     * @param  array<int, mixed>        $params
     * @return array<int,        mixed>
     */
    public function testDeleteComment(array $params): array
    {
        [$item, $comment] = $params;

        $comment->delete();

        $item->refresh();

        $this->assertSame(0, $item->total_comment);

        return $params;
    }
}
