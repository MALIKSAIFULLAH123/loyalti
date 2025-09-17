<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;

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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => 1,
            'quiz_id'       => 1,
            'user_type'     => 'user',
            'total_correct' => rand(1, 10),
        ];
    }

    public function forQuiz(Quiz $quiz): self
    {
        return $this->state(fn () => ['quiz_id' => $quiz->entityId()]);
    }

    public function setQuiz(Quiz $quiz): self
    {
        return $this->state(function () use ($quiz) {
            return [
                'quiz_id' => $quiz->entityId(),
            ];
        });
    }

    public function seed()
    {
        return $this;
    }
}

// end
