<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Answer;

use MetaFox\Poll\Http\Resources\v1\Answer\AnswerItem as Resource;
use MetaFox\Poll\Http\Resources\v1\Answer\AnswerItemCollection as ResourceCollection;
use MetaFox\Poll\Models\Answer as Model;
use Tests\TestCase;

class AnswerItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        /** @var Model $model */
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
