<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Category;

use MetaFox\Marketplace\Http\Resources\v1\Category\CategoryItem as Resource;
use MetaFox\Marketplace\Http\Resources\v1\Category\CategoryItemCollection as ResourceCollection;
use MetaFox\Marketplace\Models\Category as Model;
use Tests\TestCase;

class CategoryItemTest extends TestCase
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
     * @param \MetaFox\Marketplace\Models\Category $model
     */
    public function testResource(Model $model)
    {
        $resource = new Resource($model);

        $resource->toJson();

        $this->assertTrue(true);
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->assertTrue(true);
    }
}
