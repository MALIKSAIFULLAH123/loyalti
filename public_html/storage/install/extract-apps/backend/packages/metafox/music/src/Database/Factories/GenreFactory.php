<?php

namespace MetaFox\Music\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Music\Models\Genre;

/**
 * Class  GenreFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class GenreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Genre::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->word,
            'is_active' => random_value(5, 0, 1),
        ];
    }
}
