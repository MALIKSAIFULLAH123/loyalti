<?php

namespace MetaFox\Like\Tests\Unit\Http\Resources\v1\Like;

use MetaFox\Like\Http\Resources\v1\Like\LikeItem as Resource;
use MetaFox\Like\Http\Resources\v1\Like\LikeItemCollection as ResourceCollection;
use MetaFox\Like\Models\Like as Model;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreate(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item  = ContentModel::factory()->setOwner($user)->setUser($user)->create();
        $model = Model::factory()->setUser($user)->setItem($item)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return [$user, $model];
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $data
     */
    public function testResource(array $data)
    {
        $this->markTestIncomplete();
        [$user, $model] = $data;
        $this->be($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $data
     */
    public function testCollection(array $data)
    {
        $this->markTestIncomplete();
        [$user, $model] = $data;
        $this->be($user);

        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
