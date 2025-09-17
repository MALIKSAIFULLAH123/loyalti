<?php

namespace MetaFox\Saved\Tests\Unit\Http\Resources\v1\Saved;

use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedItem as Resource;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedItemCollection as ResourceCollection;
use MetaFox\Saved\Models\Saved as Model;
use Tests\TestCase;

class SavedItemTest extends TestCase
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
        $resource = new Resource($model);

        $this->actingAs($model->user);

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
        $this->actingAs($model->user);
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
