<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\CancelFeedback;

/**
 * Class CancelFeedbackFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method CancelFeedback create($attributes = [], ?Model $parent = null)
 */
class CancelFeedbackFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CancelFeedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email'         => $this->faker->email,
            'name'          => $this->faker->sentence,
            'user_group_id' => 2,
            'feedback_text' => $this->faker->text,
        ];
    }
}

// end
