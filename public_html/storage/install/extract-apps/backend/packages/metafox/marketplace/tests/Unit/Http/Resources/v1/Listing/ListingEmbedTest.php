<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingEmbed as Resource;
use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingEmbedCollection as ResourceCollection;
use MetaFox\Marketplace\Models\Listing as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
| @link MetaFox\Marketplace\Http\Resources\v1\Listing\ListingEmbed
*/

class ListingEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
        $model = Model::factory()->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

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
        $this->markTestIncomplete();
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
