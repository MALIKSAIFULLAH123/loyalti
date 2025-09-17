<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Blog;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\User\Models\User;
use Tests\TestCase;

class BlogUpdatePolicyTest extends TestCase
{
    use TestBlogPolicy;

    public function testCanModerate()
    {
        $blog = Blog::factory()->makeOne();

        $user   = \Mockery::mock(User::class);
        $policy = new BlogPolicy();

        $this->mockUserPermissions($user, [
            'blog.moderate' => true,
            'blog.update'   => false,
        ]);

        $this->assertTrue($policy->update($user, $blog));
    }

    public function testCanNotUpdatePermission()
    {
        $mockResource = Blog::factory()->makeOne();

        $mockUser = \Mockery::mock(User::class);
        $policy   = new BlogPolicy();

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.update'   => false,
        ]);

        $this->assertFalse($policy->update($mockUser, $mockResource));
    }

    public function testCanUpdateTruthy()
    {
        $mockResource = Blog::factory(['user_id' => 1])->makeOne();
        $mockUser     = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $policy       = new BlogPolicy();

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.update'   => true,
        ]);

        $this->assertTrue($policy->update($mockUser, $mockResource));
    }

    public function testCanUpdateFalsy()
    {
        $mockResource = Blog::factory(['user_id' => 2])->makeOne();
        $mockUser     = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $policy       = new BlogPolicy();

        $this->mockUserPermissions($mockUser, [
            'blog.moderate' => false,
            'blog.update'   => true,
        ]);

        $this->assertFalse($policy->update($mockUser, $mockResource));
    }
}
