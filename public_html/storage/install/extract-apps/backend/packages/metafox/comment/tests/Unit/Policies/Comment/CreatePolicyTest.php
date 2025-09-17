<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class CreatePolicyTest extends TestCase
{
    use TestUserPermissions;
    use TestCommentPolicy;

    public static function provideUserPermisions()
    {
        return [
            [['comment.comment' => false], 'create', false],
            [['comment.moderate' => true], 'update', true],
            [['comment.moderate' => true], 'delete', true],
            [[], 'share', false],
            [['comment.comment' => false], 'create', false],
        ];
    }

    public function testUserCreateCommentByNullOwner()
    {
        $mockUser   = $this->mockUser();
        $mockPolicy = $this->mockPolicy();

        $this->assertFalse($mockPolicy->create($mockUser, null));
    }

    public function testUserCommentByFalsyResourcePermission()
    {
        $mockUser   = $this->mockUser();
        $mockPolicy = $this->mockPolicy();
        /** @var Comment|MockInterface $mockResource */
        $mockResource = $this->mockResource();

        $mockUser->shouldReceive('hasPermissionTo')
            ->with('comment.comment')
            ->andReturn(true);

        $mockResource->shouldReceive('entityType')
            ->andReturn('blog');

        $mockUser->shouldReceive('hasPermissionTo')
            ->with('blog.comment')
            ->andReturn(false);

        $this->assertFalse($mockPolicy->create($mockUser, $mockResource));

        $mockResource->shouldHaveReceived('entityType');
        $mockUser->shouldHaveReceived('hasPermissionTo');
    }

    public function testUserCreateCommentWithResourcePermission()
    {
        $user   = $this->mockUser();
        $policy = $this->mockPolicy();
        /** @var Comment|MockInterface $resource */
        $resource = $this->mockResource();

        $user->shouldReceive('hasPermissionTo')
            ->with('comment.comment')
            ->andReturn(true);

        $resource->shouldReceive('entityType')
            ->andReturn('blog');

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn(1);

        $resource->shouldReceive('ownerId')
            ->with()
            ->andReturn(1);

        $user->shouldReceive('hasPermissionTo')
            ->with('blog.comment')
            ->andReturn(true);

        $this->assertTrue($policy->create($user, $resource));

        $resource->shouldHaveReceived('entityType');
        $user->shouldHaveReceived('hasPermissionTo');
    }

    public static function provideUserCreateCommentWithOthers()
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    /**
     * @return void
     * @dataProvider  provideUserCreateCommentWithOthers
     */
    public function testUserCreateCommentWithOthers(bool $canCreate, bool $expected)
    {
        $user   = $this->mockUser();
        $policy = $this->mockPolicy();
        /** @var Comment|MockInterface $resource */
        $resource = $this->mockResource();

        $user->shouldReceive('hasPermissionTo')
            ->with('comment.comment')
            ->andReturn(true);

        $resource->shouldReceive('entityType')
            ->andReturn('blog');

        $user->shouldReceive('entityId')
            ->with()
            ->andReturn(1);

        $resource->shouldReceive('ownerId')
            ->with()
            ->andReturn(2);

        $user->shouldReceive('hasPermissionTo')
            ->with('blog.comment')
            ->andReturn(true);

        $privacyPolicy = PrivacyPolicy::spy();

        $resource->owner = $owner = new \MetaFox\User\Models\User();

        $privacyPolicy->shouldReceive('checkCreateOnOwner')
            ->with($user, $owner)
            ->andReturn($canCreate);

        $this->assertSame($expected, $policy->create($user, $resource));

        $resource->shouldHaveReceived('entityType');
        $user->shouldHaveReceived('hasPermissionTo');
    }
}
