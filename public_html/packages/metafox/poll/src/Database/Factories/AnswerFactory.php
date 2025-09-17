<?php

namespace MetaFox\Poll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;

class AnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Answer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poll_id'    => 1,
            'answer'     => $this->faker->sentence,
            'percentage' => 0.0,
            'total_vote' => 0,
            'ordering'   => 0,
        ];
    }

    /**
     * @param  Poll          $poll
     * @return AnswerFactory
     */
    public function setPoll(Poll $poll): self
    {
        return $this->state(function () use ($poll) {
            return [
                'poll_id' => $poll->entityId(),
            ];
        });
    }
}
