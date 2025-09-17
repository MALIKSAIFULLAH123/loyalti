<?php

namespace MetaFox\Storage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Storage\Models\Asset;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class AssetFactory.
 * @method Asset create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class AssetFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

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
