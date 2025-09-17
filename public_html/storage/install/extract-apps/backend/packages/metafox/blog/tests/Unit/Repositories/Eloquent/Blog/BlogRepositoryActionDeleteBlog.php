<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Exception;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Models\BlogText;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryActionDeleteBlog extends TestCase
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
     * @throws Exception
     */
    public function testDeleteBlog(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->deleteBlog($admin, $item->id);
        $this->assertEmpty(Model::query()->find($item->id));
        $this->assertEmpty(BlogText::query()->find($item->id));
    }
}
