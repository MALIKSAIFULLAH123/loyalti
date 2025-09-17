<?php

namespace MetaFox\Quiz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;

class QuizFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quiz::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'view_id'          => 0,
            'user_id'          => 1,
            'user_type'        => 'user',
            'owner_id'         => 1,
            'owner_type'       => 'user',
            'privacy'          => random_privacy(),
            'title'            => $this->faker->sentence,
            'text'             => $this->faker->text,
            'is_featured'      => 0,
            'is_sponsor'       => 0,
            'is_approved'      => 1,
            'sponsor_in_feed'  => 0,
            'total_like'       => 0,
            'total_share'      => 0,
            'total_comment'    => 0,
            'total_attachment' => 0,
            'total_view'       => 0,
            'image_file_id'    => $this->sampleFile('photo', '*', 80)?->id,
        ];
    }

    public function setQuestions($questions = []): self
    {
        if (empty($questions)) {
            $answers = [];
            $count   = mt_rand(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $answers[] = ['answer' => $this->faker->sentence, 'ordering' => $i];
            }
            for ($i = 0; $i < $count; $i++) {
                $questions[] = ['question' => $this->faker->sentence, 'ordering' => $i, 'answers' => $answers];
            }
        }

        return $this->state(function () use ($questions) {
            return ['questions' => $questions];
        });
    }

    public function createQuestions()
    {
        $answers = [];
        $count   = mt_rand(2, 5);
        for ($i = 0; $i < $count; $i++) {
            $answers[] = ['answer' => $this->faker->sentence, 'ordering' => $i];
        }
        for ($i = 0; $i < $count; $i++) {
            $questions[] = ['question' => $this->faker->sentence, 'ordering' => $i, 'answers' => $answers];
        }

        return $questions;
    }

    public function seed()
    {
        return $this->hasQuestions($this->faker->numberBetween(2, 5));
    }

    public function configure()
    {
        return parent::configure()
            ->has(Question::factory()->times(2));
    }
}
