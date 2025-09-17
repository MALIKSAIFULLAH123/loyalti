<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Blog;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Policies\BlogPolicy as Policy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Models\User;
use Tests\TestCase;

class BlogViewPolicyTest extends TestCase
{
    use TestBlogPolicy;

    public function testModerate()
    {
        // test has permission to blog.moderate
        $mockPolicy = \Mockery::mock(Policy::class)->makePartial();

        $mockUser = \Mockery::mock(\MetaFox\User\Models\User::class);

        $this->mockUserPermissions($mockUser, ['blog.moderate' => true]);

        $this->assertTrue($mockPolicy->view($mockUser, new Blog()));
    }

    public function testModerateWithoutPolicy()
    {
        // test has permission to blog.moderate
        $mockPolicy = \Mockery::mock(new Policy());

        $mockUser = \Mockery::mock(User::class);

        $resource = \Mockery::mock(Blog::class);

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.view'     => false,
        ]);

        $this->assertFalse($mockPolicy->view($mockUser, $resource));
    }

    public function testDoesNotHaveViewOwnerPermissions()
    {
        // test has permission to blog.moderate
        $mockPolicy = \Mockery::mock(Policy::class)->makePartial();

        $mockPolicy->shouldReceive('viewOwner')
            ->andReturn(false);

        $mockUser = \Mockery::mock(User::class)->makePartial();

        $mockResource = \Mockery::mock(new Blog([
            'is_published' => 1,
            'is_approved'  => 1,
        ]))->makePartial();

        $mockResource->owner = new User(['id' => 1]);

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.view'     => true,
        ]);

        $this->assertFalse($mockPolicy->view($mockUser, $mockResource));

        $mockPolicy->shouldHaveReceived('viewOwner')->withAnyArgs();
    }

    public function testViewTheirOwnBlog()
    {
        // test has permission to blog.moderate
        $mockPolicy = \Mockery::mock(Policy::class)->makePartial();

        $mockPolicy->shouldReceive('viewOwner')
            ->withAnyArgs()
            ->andReturn(true);

        $mockUser = \Mockery::mock(new User(['id' => 1]))->makePartial();

        $mockResource = \Mockery::mock(new Blog([
            'is_draft'    => 1,
            'is_approved' => 1,
            'user_id'     => 1,
            'privacy'     => 0,
        ]))->makePartial();

        PrivacyPolicy::spy();

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.view'     => true,
        ]);

        $mockResource->owner = new User(['id' => 1]);

        PrivacyPolicy::shouldReceive('checkPermission')
            ->withArgs([$mockUser, $mockResource])
            ->andReturn(true);

        $this->assertTrue($mockPolicy->view($mockUser, $mockResource));

        $mockPolicy->shouldHaveReceived('viewOwner');

        PrivacyPolicy::partialMock()
            ->shouldHaveReceived('checkPermission')
            ->with($mockUser, $mockResource);
    }
}
