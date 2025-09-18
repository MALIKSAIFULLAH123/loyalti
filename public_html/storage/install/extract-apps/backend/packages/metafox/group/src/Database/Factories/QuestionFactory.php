<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Question;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class QuestionFactory.
 * @method Question create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class QuestionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_TEXT,
        ];
    }
}

// end
