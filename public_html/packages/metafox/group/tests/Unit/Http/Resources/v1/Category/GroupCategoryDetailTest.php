<?php

namespace MetaFox\Group\Tests\Unit\Http\Resources\v1\Category;

use MetaFox\Group\Http\Resources\v1\Category\CategoryDetail as Resource;
use MetaFox\Group\Models\Category as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Group\Http\Resources\v1\GroupCategory\GroupCategoryDetail
*/

class GroupCategoryDetailTest extends TestCase
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

        $this->markTestIncomplete('coming soon!');
    }
}
