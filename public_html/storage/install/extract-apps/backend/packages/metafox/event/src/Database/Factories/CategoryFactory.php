<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \MetaFox\Event\Models\Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->name,
            'is_active' => true,
            'ordering'  => 0,
            'parent_id' => rand(1, 10) < 3 ? 0 : $this->faker->numberBetween(1, 10),
        ];
    }
}
