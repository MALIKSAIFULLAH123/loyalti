<?php

namespace MetaFox\Event\Tests\Unit\Http\Resources\v1\Category;

use MetaFox\Event\Http\Resources\v1\Category\CategoryItemCollection as ResourceCollection;
use MetaFox\Event\Models\Category as Model;
use Tests\TestCase;

class CategoryItemCollectionTest extends TestCase
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
    public function testResourceCollection(Model $model)
    {
        $model2 = Model::factory()->create();

        $resource = new ResourceCollection([$model, $model2]);
        $this->assertCount(2, $resource);

        $result = $resource->toArray(null);
        $item   = $result[0];

        $this->assertEquals($item['id'], $model->entityId());
        $this->assertEquals($item['resource_name'], $model->entityType());
        $this->assertEquals($item['name'], $model->name);
    }
}
