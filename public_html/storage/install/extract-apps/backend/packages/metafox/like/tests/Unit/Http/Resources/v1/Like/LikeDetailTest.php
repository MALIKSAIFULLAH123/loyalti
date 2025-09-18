<?php

namespace MetaFox\Like\Tests\Unit\Http\Resources\v1\Like;

use MetaFox\Like\Http\Resources\v1\Like\LikeDetail as Resource;
use MetaFox\Like\Models\Like as Model;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item  = ContentModel::factory()->setOwner($user)->setUser($user)->create();
        $model = Model::factory()->setUser($user)->setItem($item)->create();

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
        $this->markTestIncomplete();
        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
