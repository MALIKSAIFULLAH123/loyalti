<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Result;

use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultDetail as Resource;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Quiz\Http\Resources\v1\Result\ResultDetail
*/

class ResultDetailTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        /** @var Model $model */
        $model = Model::factory()->setQuiz($quiz)->setUser($user)->create();

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
        $this->actingAs($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
