<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Blog;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserPrivacy;
use Tests\TestCase;

class BlogCreatePolicyTest extends TestCase
{
    use \Tests\Traits\TestUserPermissions;
    use TestBlogPolicy;

    public function policyName(): string
    {
        return BlogPolicy::class;
    }

    public function resourceName(): string
    {
        return Blog::class;
    }

    public static function provideUserPermisions()
    {
        return [
            [['blog.create' => false], 'create', false],
        ];
    }

    public function testCanCreate()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 1])->makeOne());

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertTrue($mockPolicy->create($mockUser, $mockOwner));
    }

    public function testCanCreateWithUser()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 2])->makeOne())->makePartial();

        $mockOwner->shouldReceive('entityType')
            ->andReturn('user');

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertFalse($mockPolicy->create($mockUser, $mockOwner));
    }

    public function testCanCreateWithPage()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 2])->makeOne())->makePartial();

        $mockOwner->shouldReceive('entityType')
            ->andReturn('page');

        PrivacyPolicy::spy();
        UserPrivacy::spy();

        PrivacyPolicy::shouldReceive('checkPermissionOwner')->andReturn(true);
        PrivacyPolicy::shouldReceive('checkCreateOnOwner')->andReturn(true);
        UserPrivacy::shouldReceive('hasAccess')->andReturn(true);

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertTrue($mockPolicy->create($mockUser, $mockOwner));
    }

    public function testCanNotCreateWithPage()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 2])->makeOne())->makePartial();

        $mockOwner->shouldReceive('entityType')
            ->andReturn('page');

        PrivacyPolicy::spy();
        UserPrivacy::spy();

        PrivacyPolicy::shouldReceive('checkPermissionOwner')->andReturn(false);
        PrivacyPolicy::shouldReceive('checkCreateOnOwner')->andReturn(true);
        UserPrivacy::shouldReceive('hasAccess')->andReturn(true);

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertFalse($mockPolicy->create($mockUser, $mockOwner));
    }

    public function testCanNotCreateWithPage2()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 2])->makeOne())->makePartial();

        $mockOwner->shouldReceive('entityType')
            ->andReturn('page');

        PrivacyPolicy::spy();
        UserPrivacy::spy();

        PrivacyPolicy::shouldReceive('checkPermissionOwner')->andReturn(true);
        PrivacyPolicy::shouldReceive('checkCreateOnOwner')->andReturn(false);
        UserPrivacy::shouldReceive('hasAccess')->andReturn(true);

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertFalse($mockPolicy->create($mockUser, $mockOwner));
    }

    public function testCanNotCreateWithPage3()
    {
        $mockUser  = \Mockery::mock(User::factory(['id' => 1])->makeOne());
        $mockOwner = \Mockery::mock(User::factory(['id' => 2])->makeOne())->makePartial();

        $mockOwner->shouldReceive('entityType')
            ->andReturn('group');

        PrivacyPolicy::spy();
        UserPrivacy::spy();

        PrivacyPolicy::shouldReceive('checkPermissionOwner')->andReturn(true);
        PrivacyPolicy::shouldReceive('checkCreateOnOwner')->andReturn(true);
        UserPrivacy::shouldReceive('hasAccess')->andReturn(false);

        $mockPolicy = \Mockery::mock(BlogPolicy::class)->makePartial();

        $this->mockUserPermissions($mockUser, [
            'blog.create' => true,
        ]);

        $this->assertFalse($mockPolicy->create($mockUser, $mockOwner));
    }
}
