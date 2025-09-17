<?php

namespace MetaFox\ActivityPoint\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class ActivityPointPackageFactory.
 * @method PointPackage create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class PointPackageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PointPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'          => $this->faker->sentence,
            'server_id'      => 'public',
            'image_path'     => null,
            'amount'         => 500,
            'price'          => 20.0,
            'currency_id'    => 'USD',
            'is_active'      => 1,
            'total_purchase' => 0,
        ];
    }
}

// end
