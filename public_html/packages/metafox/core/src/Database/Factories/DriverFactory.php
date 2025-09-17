<?php

namespace MetaFox\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\Driver;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class DriverFactory.
 * @method Driver create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class DriverFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'        => $this->faker->name,
            'name'        => $this->faker->name,
            'driver'      => $this->faker->words(10),
            'title'       => $this->faker->title,
            'description' => $this->faker->sentence,
            'url'         => $this->faker->url,
            'package_id'  => $this->faker->words(20),
        ];
    }
}

// end
