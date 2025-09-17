<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryActionSponsorInFeedBlog extends TestCase
{
    /**
     * @return User
     */
    public function testCreateUserResource(): User
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $this->assertInstanceOf(User::class, $admin);

        return $admin;
    }

    /**
     * @depends testCreateUserResource
     * @param  User              $admin
     * @return array<int, mixed>
     */
    public function testInstance(User $admin): array
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);

        $item = Model::factory()->create(['privacy' => MetaFoxPrivacy::EVERYONE, 'sponsor_in_feed' => 0]);
        $this->assertNotEmpty($item);

        return [$repository, $admin, $item];
    }

    /**
     * @depends testInstance
     *
     * @param  array<int, mixed>        $params
     * @return array<int,        mixed>
     */
    public function testSponsorInFeed(array $params): array
    {
        [$repository, $admin, $item] = $params;
        $this->actingAs($admin);

        $repository->sponsorInFeed($admin, $item->id, 1);
        $item->refresh();
        $this->assertTrue(!empty($item->sponsor_in_feed));

        return $params;
    }

    /**
     * @depends testSponsorInFeed
     *
     * @param array<int, mixed> $params
     */
    public function testRemoveSponsorInFeed(array $params)
    {
        [$repository, $admin, $item] = $params;
        $this->actingAs($admin);

        $repository->sponsorInFeed($admin, $item->id, 0);
        $item->refresh();
        $this->assertTrue(empty($item->sponsor_in_feed));
    }
}
