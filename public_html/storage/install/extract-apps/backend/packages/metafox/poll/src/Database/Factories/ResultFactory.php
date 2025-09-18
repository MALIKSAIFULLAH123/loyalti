<?php

namespace MetaFox\Poll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result;

class ResultFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Result::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [];
    }

    /**
     * @param  Poll          $poll
     * @return ResultFactory
     */
    public function forPoll(Poll $poll): self
    {
        return $this->state(function () use ($poll) {
            return [
                'poll_id' => $poll->entityId(),
            ];
        });
    }

    /**
     * @param  Answer        $answer
     * @return ResultFactory
     */
    public function forAnswer(Answer $answer): self
    {
        return $this->state(function () use ($answer) {
            return [
                'answer_id' => $answer->entityId(),
            ];
        });
    }

    /**
     * @param  Poll          $poll
     * @return ResultFactory
     */
    public function setPoll(Poll $poll): self
    {
        return $this->state(function () use ($poll) {
            return [
                'poll_id' => $poll->entityId(),
            ];
        });
    }

    /**
     * @param  Answer        $answer
     * @return ResultFactory
     */
    public function setAnswer(Answer $answer): self
    {
        return $this->state(function () use ($answer) {
            return [
                'answer_id' => $answer->entityId(),
            ];
        });
    }
}
