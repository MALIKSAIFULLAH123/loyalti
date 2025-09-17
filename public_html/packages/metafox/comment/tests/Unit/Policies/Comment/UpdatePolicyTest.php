<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Platform\Contracts\Entity;
use Tests\TestCase;

class UpdatePolicyTest extends TestCase
{
    use TestCommentPolicy;

    public static function provideUserUpdateComment()
    {
        return [
            [['comment.moderate' => true], true],
            [['comment.moderate' => false, 'comment.update' => false], false],
            [['comment.moderate' => false, 'comment.update' => true], false],
        ];
    }

    /**
     * @param  array $permissions
     * @param  bool  $expected
     * @return void
     * @dataProvider provideUserUpdateComment
     */
    public function testUserUpdateComment(array $permissions, bool $expected)
    {
        $user     = $this->mockUser();
        $policy   = $this->mockPolicy();
        $resource = null;

        $this->mockUserPermissions($user, $permissions);

        $this->assertSame($expected, $policy->update($user, $resource));
    }

    public static function provideUserUpdateComments()
    {
        return [
            'case #1' => [
                [
                    'comment.moderate'           => false,
                    'comment.update_on_own_item' => false,
                    'comment.update'             => false,
                ], 1, 1, 1, false,
            ],
            'case #2' => [
                [
                    'comment.moderate'           => false,
                    'comment.update_on_own_item' => false,
                    'comment.update'             => true,
                ], 1, 1, 1, true,
            ],
            'case #3' => [
                [
                    'comment.moderate'           => false,
                    'comment.update_on_own_item' => true,
                    'comment.update'             => null,
                ], 1, 1, 1, true,
            ],
            'case #4' => [
                [
                    'comment.moderate'           => false,
                    'comment.update_on_own_item' => true,
                    'comment.update'             => null,
                ], 1, 1, 2, false,
            ],
        ];
    }

    /**
     * @param  array $permissions
     * @param  int   $mockUserId
     * @param  int   $commentUserId
     * @param  int   $itemUserId
     * @param  bool  $expected
     * @return void
     * @dataProvider  provideUserUpdateComments
     */
    public function testUserUpdateComments(
        array $permissions,
        int $mockUserId,
        int $commentUserId,
        int $itemUserId,
        bool $expected
    ) {
        $user           = $this->mockUser();
        $policy         = $this->mockPolicy();
        $resource       = $this->mockResource();
        $resource->item = \Mockery::mock(Entity::class);

        $resource->item->shouldReceive('userId')
            ->with()
            ->andReturn($itemUserId);

        $resource->shouldReceive('userId')
            ->with()
            ->andReturn($commentUserId);

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn($mockUserId);

        $this->mockUserPermissions($user, $permissions);

        $this->assertSame($expected, $policy->update($user, $resource));
    }
}
