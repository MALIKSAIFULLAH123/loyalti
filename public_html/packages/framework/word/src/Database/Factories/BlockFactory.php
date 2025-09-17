<?php

namespace MetaFox\Word\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Word\Models\Block;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class WordFactory.
 * @method Block create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class BlockFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word' => $this->faker->name,
        ];
    }
}

// end
