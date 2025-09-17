<?php

namespace MetaFox\Comment\Tests\Feature;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\Content;
use Tests\TestCase;

class CommentItemTest extends TestCase
{
    use TestFactoryContent;

    /**
     * @depends testCreateContent
     */
    public function testCommentItem(Content $item): Comment
    {
        $comment = Comment::factory()->setUser($item->user)->setItem($item)->create();

        $item->refresh();

        $this->assertSame(1, $item->total_comment);

        return $comment;
    }

    /**
     * @depends testCommentItem
     */
    public function testDeleteCommentItem(Comment $comment)
    {
        $item = $comment->item;

        $comment->delete();

        $item->refresh();

        $this->assertSame(0, $item->total_comment);
    }
}
