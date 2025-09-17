<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Models\ResultDetail;

class ResultDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ResultDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_correct' => 0,
        ];
    }

    public function setResult(Result $result): self
    {
        return $this->state(function () use ($result) {
            return [
                'result_id' => $result->entityId(),
            ];
        });
    }

    public function setQuestion(int $question): self
    {
        return $this->state(function () use ($question) {
            return [
                'question_id' => $question,
            ];
        });
    }

    public function setAnswer(Answer $answer): self
    {
        return $this->state(function () use ($answer) {
            return [
                'answer_id' => $answer->entityId(),
            ];
        });
    }
}

// end
