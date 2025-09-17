<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Support\Facades\UserPrivacy;
use Tests\TestCase;

class ViewPolicyTest extends TestCase
{
    use TestCommentPolicy;

    public function testCanNotViewApproval()
    {
        $user       = $this->mockUser();
        $resource   = $this->mockResource();
        $mockPolicy = $this->mockPolicy();

        $mockPolicy->shouldReceive('viewApprove')
            ->with($user, $resource)
            ->andReturn(false);

        $this->assertFalse($mockPolicy->view($user, $resource));
        $mockPolicy->shouldHaveReceived('viewApprove');
    }

    public function testCanViewApproval()
    {
        $user       = $this->mockUser();
        $resource   = $this->mockResource();
        $mockPolicy = $this->mockPolicy();

        $mockPolicy->shouldReceive('viewApprove')
            ->with($user, $resource)
            ->andReturn(true);

        $this->assertTrue($mockPolicy->view($user, $resource));

        $mockPolicy->shouldHaveReceived('viewApprove');
    }

    public static function provideViewOwner()
    {
        return [
            [false, null, false],
            [true, false, false],
            [true, true, true],
        ];
    }

    /**
     * @dataProvider  provideViewOwner
     * @param  bool|null $permissonOwner
     * @param  bool|null $hasAccess
     * @param  bool      $expected
     * @return void
     */
    public function testCanViewOwner(?bool $permissonOwner, ?bool $hasAccess, bool $expected)
    {
        $mockUser = $this->mockUser();

        /** @var CommentPolicy $mockPolicy */
        $mockPolicy = $this->mockPolicy();

        $mockOtherUser = $this->mockUser();

        if (null !== $permissonOwner) {
            $mockPrivacyPolicy = PrivacyPolicy::spy();

            $mockPrivacyPolicy->shouldReceive('checkPermissionOwner')
                ->with($mockUser, $mockOtherUser)
                ->andReturn(true);
        }

        if (null !== $hasAccess) {
            $mockUserPolicy = UserPrivacy::spy();

            $mockUserPolicy->shouldReceive('hasAccess')
                ->with($mockUser, $mockOtherUser, 'comment.view_browse_comments')
                ->andReturn(true);
        }

        $this->assertTrue($mockPolicy->viewOwner($mockUser, $mockOtherUser));

        if (null !== $permissonOwner) {
            $mockPrivacyPolicy->shouldHaveReceived('checkPermissionOwner');
        }

        if (null !== $hasAccess) {
            $mockUserPolicy->shouldHaveReceived('hasAccess');
        }
    }
}
