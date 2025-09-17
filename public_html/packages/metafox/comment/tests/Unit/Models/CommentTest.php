<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Comment\Tests\Unit\Models;

use MetaFox\Comment\Database\Factories\CommentFactory;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Comment\Tests\Feature\TestFactoryContent;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\PolicyGate;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use TestFactoryContent;

    public function testPolicy()
    {
        $policy = PolicyGate::getPolicyFor(Comment::class);
        $this->assertNotNull($policy);
    }

    /**
     * @return void
     * @depends  testCreateContent
     */
    public function testCreateComment(Content $item)
    {
        $user = $item->user;

        $this->be($user);

        $comment = CommentFactory::new([])
            ->setUser($user)
            ->setItem($item)
            ->create();

        $comment->refresh();
        $this->assertNotNull($comment->is_hidden);

        $edgerUser  = $comment->user;
        $morphOwner = $comment->owner;
        $morphItem  = $comment->item;

        $this->assertEquals($edgerUser->entityType(), $user->entityType());
        $this->assertEquals($edgerUser->entityId(), $user->entityId());
        $this->assertEquals($morphOwner->entityType(), $user->entityType());
        $this->assertEquals($morphOwner->entityId(), $user->entityId());
        $this->assertEquals($morphItem->entityType(), $item->entityType());
        $this->assertEquals($morphItem->entityId(), $item->entityId());
        $this->assertCount(1, $morphItem->comments);

        $history = new CommentHistory(['comment_id' => $comment->id,
            'user_id'                               => $comment->userId(),
            'user_type'                             => $comment->userType(),
            'content'                               => 'this',
        ]);

        $history->save();

        $comment->refresh();
        $this->assertEquals(1, $comment->is_edited);

        $comment->delete();

        $morphItem->refresh();

        $this->assertCount(0, $morphItem->comments);
    }
}
