<?php

namespace MetaFox\Quiz\Tests\Unit\Models;

use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use Tests\TestCases\TestEntityModel;

class ResultTest extends TestEntityModel
{
    public function modelName(): string
    {
        return Result::class;
    }

    public function makeOne($user)
    {
        $other = $this->createNormalUser();
        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz = Quiz::factory()
            ->forUser($user)
            ->forOwner($user)
            ->create();

        $result = Result::factory()
            ->setUser($other)
            ->forQuiz($quiz)
            ->makeOne();

        return $result;
    }

    /**
     * A basic unit test example.
     *
     * @return void
     * @depends testFindById
     */
    public function testValidateStored($model)
    {
        $this->assertInstanceOf(Result::class, $model);
        $this->assertNotEmpty($model->entityId());
        $this->assertNotEmpty($model->user->entityId());
        $this->assertNotEmpty($model->user->entityType());
    }
}

// end
