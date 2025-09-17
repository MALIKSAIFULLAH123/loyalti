<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Result;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Http\Resources\v1\Result\ResultItem as Resource;
use MetaFox\Poll\Http\Resources\v1\Result\ResultItemCollection as ResourceCollection;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result as Model;
use Tests\TestCase;

class ResultItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Answer $answer */
        $answer = Answer::factory()->setPoll($poll)->create();

        /** @var Model $model */
        $model = Model::factory()->setUser($user)->setPoll($poll)->setAnswer($answer)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());
        $this->assertNotEmpty($model->user->entityId());

        return $model;
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testResource(Model $model)
    {
        $this->asAdminUser();
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
        $this->asAdminUser();

        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
