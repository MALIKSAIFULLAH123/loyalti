<?php

namespace MetaFox\Saved\Tests\Unit\Http\Resources\v1\Saved;

use MetaFox\Activity\Models\Post;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedDetail as Resource;
use MetaFox\Saved\Models\Saved as Model;
use Tests\TestCase;

class SavedDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);
        $item  = $this->contentFactory()->setUser($user)->setOwner($user)->create();
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
        $this->actingAs($model->user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
