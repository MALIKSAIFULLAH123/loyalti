<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Answer;

use MetaFox\Quiz\Http\Resources\v1\Answer\AnswerItem as Resource;
use MetaFox\Quiz\Http\Resources\v1\Answer\AnswerItemCollection as ResourceCollection;
use MetaFox\Quiz\Models\Answer as Model;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class AnswerItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $quiz = Quiz::factory()->create();
        $this->assertInstanceOf(Quiz::class, $quiz);

        $question = Question::factory()->setQuiz($quiz)->create();
        $this->assertInstanceOf(Question::class, $question);

        /** @var Model $model */
        $model = Model::factory()->setQuestion($question)->create();

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
