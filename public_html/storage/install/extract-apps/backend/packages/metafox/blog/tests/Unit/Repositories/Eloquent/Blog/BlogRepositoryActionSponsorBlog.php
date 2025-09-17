<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryActionSponsorBlog extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);

        $item = Model::factory()->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     *
     * @param Model $item
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function testSponsorBlog(Model $item): Model
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->sponsor($admin, $item->id, 1);
        $item->refresh();
        $this->assertTrue(!empty($item->is_sponsor));

        return $item;
    }

    /**
     * @depends testSponsorBlog
     *
     * @param Model $item
     *
     * @throws AuthorizationException
     */
    public function testRemoveSponsorBlog(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->sponsor($admin, $item->id, 0);
        $item->refresh();
        $this->assertTrue(empty($item->is_sponsor));
    }
}
