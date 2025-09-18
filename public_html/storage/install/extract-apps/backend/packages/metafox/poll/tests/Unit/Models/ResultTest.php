<?php

namespace MetaFox\Poll\Tests\Unit\Models;

use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result;
use Tests\TestCases\TestEntityModel;

class ResultTest extends TestEntityModel
{
    public function modelName(): string
    {
        return Result::class;
    }

    public function makeOne($user)
    {
        $this->actingAs($user);

        $poll = Poll::factory()
            ->has(Answer::factory())
            ->create();

        $answer = $poll->answers->first();

        Result::factory()
            ->forUser($user)
            ->forPoll($poll)
            ->forAnswer($answer)
            ->create();

        return Result::query()->where(['poll_id' => $poll->getKey()])->firstOrFail();
    }

    /**
     * @param $model
     * @return void
     * @depends testFindById
     */
    public function testValidateStored($model)
    {
        $this->assertInstanceOf(Result::class, $model);
        $this->assertInstanceOf(ContractUser::class, $model->user);
        $this->assertInstanceOf(Answer::class, $model->answer);
    }
}

// end
