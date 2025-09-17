<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Resources\v1\StatusBackground;

use MetaFox\BackgroundStatus\Http\Resources\v1\StatusBackground\StatusBackgroundDetail as Resource;
use MetaFox\BackgroundStatus\Models\StatusBackground as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\BackgroundStatus\Http\Resources\v1\StatusBackground\StatusBackgroundDetail
*/

class StatusBackgroundDetailTest extends TestCase
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
}
