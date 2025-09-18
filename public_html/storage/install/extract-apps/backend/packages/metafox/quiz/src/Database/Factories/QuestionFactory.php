<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    protected int $ordering = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id'  => 1,
            'ordering' => $this->ordering++,
            'question' => $this->faker->sentence,
        ];
    }

    public function setQuiz(Quiz $quiz): self
    {
        return $this->state(function () use ($quiz) {
            return [
                'quiz_id' => $quiz->entityId(),
            ];
        });
    }

    public function setAnswers($answers = []): self
    {
        if (empty($answers)) {
            $count = mt_rand(2, 4);
            for (; $count > 0; $count++) {
                $answers[] = AnswerFactory::new()->definition();
            }
        }

        return $this->state(function () use ($answers) {
            return [
                'answers' => $answers,
            ];
        });
    }

    public function seed()
    {
        $this->ordering = 0;

        return $this->has(Answer::factory()->count($this->faker->numberBetween(1, 3)));
    }

    public function configure()
    {
        return $this->has(Answer::factory()->times(2));
    }
}
