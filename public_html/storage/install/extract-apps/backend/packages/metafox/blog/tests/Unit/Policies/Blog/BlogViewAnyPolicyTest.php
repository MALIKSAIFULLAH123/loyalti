<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Blog;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Policies\BlogPolicy as Policy;
use MetaFox\User\Models\User;
use Tests\TestCase;

class BlogViewAnyPolicyTest extends TestCase
{
    use TestBlogPolicy;

    public function testHaveModeratePermission()
    {
        // test has permission to blog.moderate
        $policy = \Mockery::mock(new Policy());

        $user = \Mockery::mock(User::class);

        $this->mockUserPermissions($user, [
            'blog.moderate' => true,
        ]);

        $this->assertTrue($policy->viewAny($user));
    }

    public function testHasViewPermissions()
    {
        // test has permission to blog.moderate
        $policy = \Mockery::mock(new Policy());

        $user = \Mockery::mock(new User(['id' => 1]));

        $this->mockUserPermissions($user, [
            'blog.moderate' => false,
            'blog.view'     => true,
        ]);

        $this->assertTrue($policy->viewAny($user));
    }

    public function testHasPermission()
    {
        // test has permission to blog.moderate
        $mockPolicy = \Mockery::mock(Policy::class)->makePartial();

        $user = \Mockery::mock(User::class);

        $this->mockUserPermissions($user, [
            'blog.moderate' => false,
            'blog.view'     => true,
        ]);

        $mockPolicy->shouldReceive('viewOwner')
            ->withArgs([$user, $user])
            ->andReturn(false);

        $this->assertFalse($mockPolicy->viewAny($user, $user));
    }
}
