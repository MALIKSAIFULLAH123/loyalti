<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\CountryChild;

/**
 * Class CountryChildFactory.
 * @method CountryChild create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Localize\Models\CountryChild
 */
class CountryChildFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CountryChild::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'state_iso'     => $this->faker->regexify('[a-zA-Z]{1}[0-9]{1}'),
            'state_code'    => $this->faker->numberBetween(1, 200),
            'geonames_code' => $this->faker->numberBetween(1, 200),
            'fips_code'     => $this->faker->regexify('[a-zA-Z]{1}[0-9]{1}'),
            'post_codes'    => [],
            'timezone'      => $this->faker->timezone,
            'country_iso'   => $this->faker->regexify('[a-zA-Z]{1}[0-9]{1}'),
            'name'          => $this->faker->city,
            'ordering'      => $this->faker->numberBetween(1, 100),
        ];
    }
}
