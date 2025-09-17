<?php

namespace MetaFox\Search\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Search\Models\Type;

/**
 * @method Type create($attributes = [], ?Model $parent = null)
 */
class TypeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Type::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'            => $this->faker->sentence(5),
            'module_id'       => 'test',
            'is_active'       => true,
            'title'           => $this->faker->sentence(5),
            'description'     => $this->faker->sentence(5),
            'is_system'       => 0,
            'can_search_feed' => true,
        ];
    }

    /**
     * Clear cache.
     *
     * @return self
     */
    public function seed()
    {
        return $this->afterCreating(function () {
            // resolve(TypeManager::class)->refresh();
        });
    }
}

// end
