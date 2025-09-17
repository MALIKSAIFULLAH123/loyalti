<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\Country;

/**
 * Class CountryFactory.
 * @method Country create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_iso' => $this->faker->regexify('[a-zA-Z]{1}[0-9]{1}'),
            'name'        => $this->faker->text(5),
            'is_active'   => $this->faker->numberBetween(0, 1),
            'ordering'    => $this->faker->numberBetween(1, 100),
        ];
    }
}
