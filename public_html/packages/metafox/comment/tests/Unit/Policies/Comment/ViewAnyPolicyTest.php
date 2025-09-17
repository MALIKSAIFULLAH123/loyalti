<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Policies\CommentPolicy;
use Mockery\MockInterface;
use Tests\TestCase;

class ViewAnyPolicyTest extends TestCase
{
    use TestCommentPolicy;

    public function testViewAnySuccessful()
    {
        $mockUser = $this->mockUser();

        /** @var CommentPolicy $mockPolicy */
        $mockPolicy = $this->mockPolicy();

        $this->assertTrue($mockPolicy->viewAny($mockUser, null));

        $this->assertTrue($mockPolicy->viewAny($mockUser, $mockUser));
    }

    public function testCanNotViewAny()
    {
        $mockUser = $this->mockUser();

        /** @var CommentPolicy|MockInterface $mockPolicy */
        $mockPolicy = $this->mockPolicy();

        $mockOtherUser = $this->mockUser();

        $mockPolicy->shouldReceive('viewOwner')
            ->with($mockUser, $mockOtherUser)
            ->andReturn(false);

        $this->assertFalse($mockPolicy->viewAny($mockUser, $mockOtherUser));

        $mockPolicy->shouldHaveReceived('viewOwner');
    }

    public function testCanNotViewAnyViewOwner()
    {
        $mockUser = $this->mockUser();

        /** @var CommentPolicy|MockInterface $mockPolicy */
        $mockPolicy = $this->mockPolicy();

        $mockOtherUser = $this->mockUser();

        $mockPolicy->shouldReceive('viewOwner')
            ->with($mockUser, $mockOtherUser)
            ->andReturn(true);

        $this->assertTrue($mockPolicy->viewAny($mockUser, $mockOtherUser));

        $mockPolicy->shouldHaveReceived('viewOwner');
    }
}
