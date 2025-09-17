<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizItem as Resource;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizItemCollection as ResourceCollection;
use MetaFox\Quiz\Models\Quiz as Model;
use Tests\TestCase;

class QuizItemTest extends TestCase
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
     * @param  Model $model
     * @return User
     */
    public function testResource(Model $model): User
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);

        return $user;
    }

    /**
     * @depends testCreate
     * @depends testResource
     * @param Model $model
     * @param User  $user
     */
    public function testCollection(Model $model, User $user)
    {
        $this->actingAs($user);
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
