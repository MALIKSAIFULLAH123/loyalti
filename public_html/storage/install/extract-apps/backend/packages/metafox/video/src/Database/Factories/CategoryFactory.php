<?php

namespace MetaFox\Video\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Video\Models\Category;

class CategoryFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->name,
            'is_active' => true,
            'ordering'  => 0,
            'parent_id' => 0,
        ];
    }
}

// end
