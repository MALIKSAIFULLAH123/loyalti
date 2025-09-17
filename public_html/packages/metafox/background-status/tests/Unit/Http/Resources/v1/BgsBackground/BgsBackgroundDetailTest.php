<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Resources\v1\BgsBackground;

use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundDetail as Resource;
use MetaFox\BackgroundStatus\Models\BgsBackground as Model;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use Tests\TestCase;

class BgsBackgroundDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $bgsCollection = BgsCollection::factory()->create();
        $model = Model::factory()->create(['collection_id' => $bgsCollection->entityId()]);

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
