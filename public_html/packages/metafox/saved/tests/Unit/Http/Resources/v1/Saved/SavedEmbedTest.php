<?php

namespace MetaFox\Saved\Tests\Unit\Http\Resources\v1\Saved;

use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedEmbed as Resource;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedEmbedCollection as ResourceCollection;
use MetaFox\Saved\Models\Saved as Model;
use Tests\TestCase;

class SavedEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
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
