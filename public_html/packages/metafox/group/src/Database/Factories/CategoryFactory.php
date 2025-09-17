<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Category;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class CategoryFactory.
 * @method Category create($attributes = [], ?Model $parent = null)
 * @ignore
 */
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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => $this->faker->name,
            'is_active' => 1,
            'parent_id' => null,
        ];
    }
}
