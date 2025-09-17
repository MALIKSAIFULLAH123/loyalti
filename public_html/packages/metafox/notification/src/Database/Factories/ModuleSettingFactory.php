<?php

namespace MetaFox\Notification\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Notification\Models\ModuleSetting;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class ModuleSettingFactory.
 * @method ModuleSetting create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class ModuleSettingFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModuleSetting::class;

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
