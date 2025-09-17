<?php

namespace MetaFox\Poll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Design;
use MetaFox\Poll\Models\Poll;

/**
 * @method Poll create($attributes = [], ?Model $parent = null)
 */
class PollFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Poll::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->faker->text;

        return [
            'view_id'          => 0,
            'privacy'          => 0,
            'user_id'          => 1,
            'user_type'        => 'user',
            'owner_id'         => 1,
            'owner_type'       => 'user',
            'question'         => $this->faker->sentence,
            'text'             => $text,
            'randomize'        => 0,
            'public_vote'      => 1,
            'is_multiple'      => 0,
            'closed_at'        => null,
            'is_featured'      => 0,
            'is_sponsor'       => 0,
            'sponsor_in_feed'  => 1,
            'total_like'       => 0,
            'total_share'      => 0,
            'total_comment'    => 0,
            'total_attachment' => 0,
            'total_view'       => 0,
            'image_file_id'    => $this->sampleFile('photo', '*', 80)?->id,
        ];
    }

    /**
     * @param  array       $answers
     * @return PollFactory
     */
    public function setAnswers(array $answers = []): self
    {
        if (empty($answers)) {
            $count = mt_rand(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $answers[] = ['answer' => $this->faker->sentence, 'ordering' => $i];
            }
        }

        return $this->state(function () use ($answers) {
            return ['answers' => $answers];
        });
    }

    /**
     * @param  array       $design
     * @return PollFactory
     */
    public function setDesign(array $design = []): self
    {
        if (empty($design)) {
            $design = [
                'border'     => $this->faker->numberBetween(1, 4),
                'percentage' => $this->faker->numberBetween(0, 100),
                'background' => $this->faker->hexColor,
            ];
        }

        return $this->state(function () use ($design) {
            return ['design' => $design];
        });
    }

    public function configure()
    {
        return parent::configure()
            ->has(Design::factory())
            ->has(Answer::factory()->times($this->faker->numberBetween(2, 4)));
    }
}
