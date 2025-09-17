<?php

namespace MetaFox\Forum\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\PermissionConfig;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class PermissionConfigFactory
 * @method PermissionConfig create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class PermissionConfigFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PermissionConfig::class;

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
