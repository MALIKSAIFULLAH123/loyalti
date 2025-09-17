<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Category;

use MetaFox\Blog\Http\Resources\v1\Category\CategoryDetail as Resource;
use MetaFox\Blog\Models\Category as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Blog\Http\Resources\v1\Category\CategoryDetail
*/

class CategoryDetailTest extends TestCase
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

        $resource->toJson();

        // assert ...

        $this->assertTrue(true);
    }
}
