<?php

namespace MetaFox\Comment\Tests\Feature;

use MetaFox\Blog\Models\Blog;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;

/**
 * @mixin \Tests\TestCase
 */
trait TestFactoryContent
{
    public function testCreateContent(): Content
    {
        $user = $this->createNormalUser();
        $this->actingAs($user);

        $item = Blog::factory()
            ->setUser($user)
            ->setOwner($user)
            ->create([
                'privacy'       => 0,
                'total_comment' => 0,
                'total_like'    => 0,
            ]);

        $this->assertInstanceOf(HasTotalComment::class, $item);
        $this->assertInstanceOf(HasTotalCommentWithReply::class, $item);

        $this->assertSame(0, $item->total_comment);

        return $item;
    }
}
