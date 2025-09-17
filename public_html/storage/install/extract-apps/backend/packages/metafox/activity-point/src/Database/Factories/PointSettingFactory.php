<?php

namespace MetaFox\ActivityPoint\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\ActivityPoint\Models\PointSetting;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class PointSettingFactory.
 * @method PointSetting create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class PointSettingFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PointSetting::class;

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
