<?php

namespace MetaFox\Featured\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Featured\Models\Item;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class ItemFactory
 * @method Item create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ItemFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

// end
