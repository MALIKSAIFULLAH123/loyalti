<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Resources\v1\RecentUsed;

use MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed\RecentUsedItem as Resource;
use MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed\RecentUsedItemCollection as ResourceCollection;
use MetaFox\BackgroundStatus\Models\RecentUsed as Model;
use Tests\TestCase;

class RecentUsedItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
        $this->markTestIncomplete('coming soon!');

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

        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
