<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\Platform\UserRole;
use MetaFox\Video\Http\Resources\v1\Video\VideoDetail as Resource;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

class VideoDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        /** @var Model $model */
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
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);
        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->expectNotToPerformAssertions();
    }
}
