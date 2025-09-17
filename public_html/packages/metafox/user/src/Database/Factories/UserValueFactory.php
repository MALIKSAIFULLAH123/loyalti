<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserValue;

/**
 * Class UserValueFactory.
 * @method UserValue create($attributes = [], ?Model $parent = null)
 * @codeCoverageIgnore
 * @ignore
 */
class UserValueFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name,
            'value'         => 1,
            'default_value' => 1,
            'ordering'      => 1,
        ];
    }
}

// end
