<?php

namespace MetaFox\Event\Tests\Unit\Http\Resources\v1\Category;

use MetaFox\Event\Http\Resources\v1\Category\CategoryDetail as Resource;
use MetaFox\Event\Models\Category as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Event\Http\Resources\v1\Category\CategoryDetail
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

        $result = $resource->toArray(null);

        $this->assertEquals($result['id'], $model->entityId());
        $this->assertEquals($result['resource_name'], $model->entityType());
        $this->assertEquals($result['name'], $model->name);
    }
}
