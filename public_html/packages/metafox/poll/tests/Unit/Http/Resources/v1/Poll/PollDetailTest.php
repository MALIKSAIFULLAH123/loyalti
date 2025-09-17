<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll;

use MetaFox\Platform\UserRole;
use MetaFox\Poll\Http\Resources\v1\Poll\PollDetail as Resource;
use MetaFox\Poll\Models\Poll as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Poll\Http\Resources\v1\Poll\PollDetail
*/

class PollDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        /** @var Model $model */
        $model = Model::factory()->create();

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
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
