<?php

namespace MetaFox\Marketplace\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Marketplace\Models\ListingPrice;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class ListingPriceFactory.
 * @method ListingPrice create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ListingPriceFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ListingPrice::class;

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
