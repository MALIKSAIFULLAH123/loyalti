<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Blog;

use MetaFox\Blog\Http\Resources\v1\Blog\BlogEmbed as Resource;
use MetaFox\Blog\Http\Resources\v1\Blog\BlogEmbedCollection as ResourceCollection;
use MetaFox\Blog\Models\Blog as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
| @link MetaFox\Blog\Http\Resources\v1\Blog\BlogEmbed
*/

class BlogEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
        /** @var Model $model */
        $model = Model::factory()->create();

        $model->refresh();

        $this->assertNotEmpty($model->id);

        return $model;
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testResource(Model $model)
    {
        $resource = new Resource($model);
        $this->asAdminUser();

        $resource->toJson();

        // assert ...

        $this->assertTrue(true);
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $this->asAdminUser();
        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->assertTrue(true);
    }
}
