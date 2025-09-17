<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Rule;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class RuleFactory.
 * @method Rule create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class RuleFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => $this->faker->title,
            'description' => $this->faker->text,
            'ordering'    => 0,
        ];
    }
}

// end
