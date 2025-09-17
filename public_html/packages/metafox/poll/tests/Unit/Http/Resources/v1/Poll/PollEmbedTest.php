<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll;

use MetaFox\Poll\Http\Resources\v1\Poll\PollEmbed as Resource;
use MetaFox\Poll\Http\Resources\v1\Poll\PollEmbedCollection as ResourceCollection;
use MetaFox\Poll\Models\Poll as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
| @link MetaFox\Poll\Http\Resources\v1\Poll\PollEmbed
*/

class PollEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
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
