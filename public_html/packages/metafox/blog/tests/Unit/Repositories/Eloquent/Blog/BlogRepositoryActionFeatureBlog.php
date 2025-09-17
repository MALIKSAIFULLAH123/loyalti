<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryActionFeatureBlog extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);
        $this->assertTrue(true);

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
    public function testFeatureBlog(Model $item): Model
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->feature($admin, $item->id, 1);
        $item->refresh();
        $this->assertTrue(!empty($item->is_featured));

        return $item;
    }

    /**
     * @depends testFeatureBlog
     *
     * @param Model $item
     *
     * @throws AuthorizationException
     */
    public function testRemoveFeatureBlog(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->feature($admin, $item->id, 0);
        $item->refresh();
        $this->assertTrue(empty($item->is_featured));
    }
}
