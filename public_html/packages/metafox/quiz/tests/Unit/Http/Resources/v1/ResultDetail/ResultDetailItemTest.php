<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\ResultDetail;

use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Http\Resources\v1\ResultDetail\ResultDetailItem as Resource;
use MetaFox\Quiz\Http\Resources\v1\ResultDetail\ResultDetailItemCollection as ResourceCollection;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Models\ResultDetail as Model;
use Tests\TestCase;

class ResultDetailItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        /** @var Result $result */
        $result = Result::factory()->setQuiz($quiz)->setUser($user)->create();

        /** @var Question $question */
        $question = Question::factory()->setQuiz($quiz)->create();

        /** @var Answer $answer */
        $answer = Answer::factory()->setQuestion($question)->create();

        /** @var Model $model */
        $model = Model::factory()->setResult($result)
            ->setQuestion($question->entityId())
            ->setAnswer($answer)
            ->create();

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
        $resource = new Resource($model);

        $this->asAdminUser();

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
