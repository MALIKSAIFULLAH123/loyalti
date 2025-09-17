<?php

namespace MetaFox\Mobile\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Mobile\Models\AdMobConfig;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class AdMobConfigFactory.
 * @method AdMobConfig create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class AdMobConfigFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdMobConfig::class;

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
