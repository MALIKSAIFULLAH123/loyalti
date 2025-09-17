<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Category;

/**
 * Class CategoryFactory.
 * @method Category create($attributes = [], ?Model $parent = null)
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => $this->faker->name,
            'name_url' => $this->faker->name,
        ];
    }
}
