<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;

class AnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Answer::class;

    protected int $ordering = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ordering'   => $this->ordering++,
            'answer'     => $this->faker->sentence,
            'is_correct' => $this->ordering === 1,
        ];
    }

    /**
     * @param  Question      $question
     * @return AnswerFactory
     */
    public function setQuestion(Question $question): self
    {
        return $this->state(function () use ($question) {
            return [
                'question_id' => $question->entityId(),
            ];
        });
    }

    public function seed()
    {
        $this->ordering = 0;

        return $this;
    }
}
