<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Question;

use MetaFox\Quiz\Http\Resources\v1\Question\QuestionItem as Resource;
use MetaFox\Quiz\Http\Resources\v1\Question\QuestionItemCollection as ResourceCollection;
use MetaFox\Quiz\Models\Question as Model;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class QuestionItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $quiz = Quiz::factory()->create();
        $this->assertInstanceOf(Quiz::class, $quiz);

        /** @var Model $model */
        $model = Model::factory()->setQuiz($quiz)->create();

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
