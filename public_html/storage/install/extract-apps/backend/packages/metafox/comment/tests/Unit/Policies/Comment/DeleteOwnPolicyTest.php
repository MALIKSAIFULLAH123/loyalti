<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Page\Models\Page;
use MetaFox\User\Models\User;
use Mockery\MockInterface;
use Tests\TestCase;

class DeleteOwnPolicyTest extends TestCase
{
    use TestCommentPolicy;

    public function testUserDeleteOwnCommentByNulledResource()
    {
        $user = $this->mockUser();

        $this->mockUserPermissions($user, [
            'comment.moderate' => false,
        ]);

        /** @var CommentPolicy $policy */
        $policy = $this->mockPolicy();

        $this->assertFalse($policy->delete($user, null));
    }

    public static function provideUserDeleteOwnComment()
    {
        return [
            [1, 1, true],
        ];
    }

    /**
     * @param  int  $userId
     * @param  int  $ownerId
     * @param  bool $expected
     * @return void
     * @dataProvider  provideUserDeleteOwnComment
     */
    public function testUserDeleteOwnComment(
        int $userId,
        int $ownerId,
        bool $expected
    ) {
        $user = $this->mockUser();

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn($userId);

        $resource = $this->mockResource();

        $resource->shouldReceive('userId')
            ->with()
            ->andReturn($ownerId);
        /** @var CommentPolicy|MockInterface $policy */
        $policy = $this->mockPolicy();

        $this->assertSame($expected, $policy->deleteOwn($user, $resource));
    }

    public static function provideUserDeleteOwnComments()
    {
        return [
            [[], 1, 2, new Page(), null, true, true],
            [[], 1, 2, new Page(), null, false, false],
            [[], 1, 2, new User(), null, null, false],
            [[], 1, 2, new User(), new User(), null, false],
            [[], 1, 2, new User(), new User(['id' => 2]), null, false],
            [['comment.delete_on_own_item' => false], 1, 2, new User(), new User(['id' => 1]), null, false],
            [['comment.delete_on_own_item' => true], 1, 2, new User(), new User(['id' => 1]), null, true],
        ];
    }

    /**
     * @param  array $userPermissions
     * @param  int   $userId
     * @param  int   $ownerId
     * @param  User  $itemOwner
     * @param  mixed $resourceOwner
     * @param  bool  $setting
     * @param  bool  $expected
     * @return void
     * @dataProvider  provideUserDeleteOwnComments
     */
    public function testUserDeleteOwnComments(
        array $userPermissions,
        int $userId,
        int $ownerId,
        mixed $itemOwner,
        mixed $resourceOwner,
        ?bool $setting,
        bool $expected
    ) {
        $user                  = $this->mockUser();
        $resource              = $this->mockResource();
        $resource->item        = $itemOwner;
        $resource->item->owner = $itemOwner;
        $resource->owner       = $resourceOwner;

        if ($userPermissions) {
            $this->mockUserPermissions($user, $userPermissions);
        }

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn($userId);

        $resource->shouldReceive('userId')
            ->with()
            ->andReturn($ownerId);

        /** @var CommentPolicy|MockInterface $policy */
        $policy = $this->mockPolicy();

        if (null !== $setting) {
            $policy->shouldReceive('checkModeratorSetting')
                ->with($user, $itemOwner, 'remove_post_and_comment_on_post')
                ->andReturn($setting);
        }

        $this->assertSame($expected, $policy->deleteOwn($user, $resource));

        if (null !== $setting) {
            $policy->shouldHaveReceived('checkModeratorSetting');
        }
    }
}
