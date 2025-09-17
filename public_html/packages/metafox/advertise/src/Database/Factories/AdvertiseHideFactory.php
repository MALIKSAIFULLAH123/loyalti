<?php

namespace MetaFox\Advertise\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Advertise\Models\AdvertiseHide;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class AdvertiseHideFactory.
 * @method AdvertiseHide create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class AdvertiseHideFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdvertiseHide::class;

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
