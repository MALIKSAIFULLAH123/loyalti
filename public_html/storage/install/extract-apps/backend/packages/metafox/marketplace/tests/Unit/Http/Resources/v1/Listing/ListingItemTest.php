<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingItem as Resource;
use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingItemCollection as ResourceCollection;
use MetaFox\Marketplace\Models\Listing as Model;
use Tests\TestCase;

class ListingItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
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
        $this->markTestIncomplete();
        $resource = new Resource($model);

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
        $this->markTestIncomplete();
        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->assertTrue(true);
    }
}
