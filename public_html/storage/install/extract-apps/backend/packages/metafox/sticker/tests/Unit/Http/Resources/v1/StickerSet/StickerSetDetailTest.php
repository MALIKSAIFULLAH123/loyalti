<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Resources\v1\StickerSet;

use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetDetail as Resource;
use MetaFox\Sticker\Models\StickerSet as Model;
use Tests\TestCase;

class StickerSetDetailTest extends TestCase
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
