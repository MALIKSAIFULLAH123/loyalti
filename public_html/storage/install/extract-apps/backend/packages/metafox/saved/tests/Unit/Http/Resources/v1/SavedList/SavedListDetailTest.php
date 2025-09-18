<?php

namespace MetaFox\Saved\Tests\Unit\Http\Resources\v1\SavedList;

use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Resources\v1\SavedList\SavedListDetail as Resource;
use MetaFox\Saved\Models\SavedList as Model;
use Tests\TestCase;

class SavedListDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);
        $model = Model::factory()->setUser($user)->create();

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
        $this->actingAs($model->user);
        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
