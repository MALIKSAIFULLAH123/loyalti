<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Resources\v1\Sticker;

use MetaFox\Sticker\Http\Resources\v1\Sticker\StickerItem as Resource;
use MetaFox\Sticker\Http\Resources\v1\Sticker\StickerItemCollection as ResourceCollection;
use MetaFox\Sticker\Models\Sticker as Model;
use MetaFox\Sticker\Models\StickerSet;
use Tests\TestCase;

class StickerItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $stickerSet = StickerSet::factory()->create();
        $model = Model::factory()->setStickerSetId($stickerSet->entityId())->create();

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
