<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Resources\v1\RecentUsed;

use MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed\RecentUsedDetail as Resource;
use MetaFox\BackgroundStatus\Models\RecentUsed as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed\RecentUsedDetail
*/

class RecentUsedDetailTest extends TestCase
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
