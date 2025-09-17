<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Blog;

use MetaFox\Blog\Http\Resources\v1\Blog\BlogItem as Resource;
use MetaFox\Blog\Http\Resources\v1\Blog\BlogItemCollection as ResourceCollection;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogItemTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate()
    {
        $model = Model::factory()->create();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $model->refresh();

        $this->assertNotEmpty($model->id);
        $this->assertInstanceOf(User::class, $user);

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param array<mixed> $params
     */
    public function testResource(array $params)
    {
        /**
         * @var Model $model
         * @var User  $user
         */
        [$model, $user] = $params;

        $this->be($user);

        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->assertTrue(true);
    }

    /**
     * @depends testCreate
     *
     * @param array<mixed> $params
     */
    public function testCollection(array $params)
    {
        /**
         * @var Model $model
         * @var User  $user
         */
        [$model, $user] = $params;

        $this->be($user);

        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->assertTrue(true);
    }
}
