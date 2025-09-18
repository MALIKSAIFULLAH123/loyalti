<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Resources\v1\StickerSet;

use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetEmbed as Resource;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetEmbedCollection as ResourceCollection;
use MetaFox\Sticker\Models\StickerSet as Model;
use Tests\TestCase;

class StickerSetEmbedTest extends TestCase
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
