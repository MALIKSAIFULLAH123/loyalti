<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\User\Support\Facades\User;
use MetaFox\Video\Http\Resources\v1\Video\VideoEmbed as Resource;
use MetaFox\Video\Http\Resources\v1\Video\VideoEmbedCollection as ResourceCollection;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

class VideoEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
        /** @var Model $model */
        $model = Model::query()->first();

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
        $this->actingAs($model->user);
        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->expectNotToPerformAssertions();
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $this->actingAs($model->user);
        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->expectNotToPerformAssertions();
    }
}
