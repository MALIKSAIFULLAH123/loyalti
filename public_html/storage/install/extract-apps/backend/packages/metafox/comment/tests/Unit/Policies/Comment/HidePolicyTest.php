<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\User\Models\User;
use Tests\TestCase;

class HidePolicyTest extends TestCase
{
    use TestCommentPolicy;

    public function testUserHideCommentPolicyByNulledResource()
    {
        $policy = $this->mockPolicy();

        $this->assertFalse($policy->hide(new User(), null));
    }

    public function testUserHasModeratePolicy()
    {
        $user     = $this->mockUser();
        $resource = $this->mockResource();
        $policy   = $this->mockPolicy();

        $this->mockUserPermissions($user, [
            'comment.moderate' => true,
        ]);

        $this->assertFalse($policy->hide($user, $resource));
    }

    public static function provideUserHideComment()
    {
        return [
            [['comment.moderate' => false], 1, 1, 1, false],
            [['comment.moderate' => false], 1, 2, 1, false],
            [
                [
                    'comment.moderate' => false,
                    'comment.hide'     => false,
                ], 1, 2, 2, false,
            ],
            [
                [
                    'comment.moderate' => false,
                    'comment.hide'     => true,
                ], 1, 2, 2, true,
            ],
        ];
    }

    /**
     * @param  array $permissions
     * @param  int   $mockUserId
     * @param  int   $userId
     * @param  int   $ownerId
     * @param  bool  $expected
     * @return void
     * @dataProvider provideUserHideComment
     */
    public function testUserHideComment(
        array $permissions,
        int $mockUserId,
        int $userId,
        int $ownerId,
        bool $expected
    ) {
        $user     = $this->mockUser();
        $resource = $this->mockResource();
        $policy   = $this->mockPolicy();

        if (!empty($permissions)) {
            $this->mockUserPermissions($user, $permissions);
        }

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn($mockUserId);

        $resource->shouldReceive('userId')
            ->with()
            ->andReturn($userId);

        $resource->shouldReceive('ownerId')
            ->with()
            ->andReturn($ownerId);

        $this->assertSame($expected, $policy->hide($user, $resource));
    }
}
