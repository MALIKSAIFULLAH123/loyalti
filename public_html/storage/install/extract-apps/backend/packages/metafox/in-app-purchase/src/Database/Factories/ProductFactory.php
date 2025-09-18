<?php

namespace MetaFox\InAppPurchase\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\InAppPurchase\Models\Product;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class ProductFactory.
 * @method Product create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ProductFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

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
