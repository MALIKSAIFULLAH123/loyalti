<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Resources\v1\BgsCollection;

use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\BgsCollectionDetail as Resource;
use MetaFox\BackgroundStatus\Models\BgsCollection as Model;
use Tests\TestCase;

class BgsCollectionDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
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
        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
