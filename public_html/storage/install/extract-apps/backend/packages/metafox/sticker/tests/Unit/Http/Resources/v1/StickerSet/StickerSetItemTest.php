<?php

namespace MetaFox\Sticker\Tests\Unit\Http\Resources\v1\StickerSet;

use MetaFox\Platform\UserRole;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetItem as Resource;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetItemCollection as ResourceCollection;
use MetaFox\Sticker\Models\StickerSet as Model;
use Tests\TestCase;

class StickerSetItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreate(): array
    {
        $model = Model::factory()->create();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $model->refresh();

        $this->assertNotEmpty($model->id);

        return [$user, $model];
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $params
     */
    public function testResource(array $params)
    {
        [$user, $model] = $params;
        $this->be($user);

        $resource = new Resource($model);
        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $params
     */
    public function testCollection(array $params)
    {
        [$user, $model] = $params;
        $this->be($user);

        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
