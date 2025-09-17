<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Policies\CommentPolicy;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class DeletePolicyTest extends TestCase
{
    use TestCommentPolicy;
    use TestUserPermissions;

    public static function provideUserPermisions()
    {
        return [
            [['comment.moderate' => true], 'delete', true],
        ];
    }

    public function testUserDeleteCommentByNulledResource()
    {
        $user = $this->mockUser();

        $this->mockUserPermissions($user, [
            'comment.moderate' => false,
        ]);

        /** @var CommentPolicy $policy */
        $policy = $this->mockPolicy();

        $this->assertFalse($policy->delete($user, null));
    }

    public static function provideUserDeleteCommentByDeleteOwn()
    {
        return [
            [false, null, 1, 1, false],
            [true, null, 1, 1, true],
            [true, true, 1, 2, true],
            [true, false, 1, 2, false],
        ];
    }

    /**
     * @param  bool|null $canDelete
     * @param  bool|null $canDeleteOwn
     * @param  int       $userId
     * @param  int       $ownerId
     * @param  bool      $expected
     * @return void
     * @dataProvider  provideUserDeleteCommentByDeleteOwn
     */
    public function testUserDeleteCommentByDeleteOwn(
        ?bool $canDelete,
        ?bool $canDeleteOwn,
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

        $this->mockUserPermissions($user, [
            'comment.moderate' => false,
        ]);

        if (null !== $canDelete) {
            $user->shouldReceive('hasPermissionTo')
                ->with('comment.delete')
                ->andReturn($canDelete);
        }

        /** @var CommentPolicy|MockInterface $policy */
        $policy = $this->mockPolicy();

        if (null !== $canDeleteOwn) {
            $policy->shouldReceive('deleteOwn')
                ->with($user, $resource)
                ->andReturn($canDeleteOwn);
        }

        $this->assertSame($expected, $policy->delete($user, $resource));

        if (null !== $canDeleteOwn) {
            $policy->shouldHaveReceived('deleteOwn');
        }
    }
}
