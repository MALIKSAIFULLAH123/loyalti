<?php

namespace MetaFox\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Subscription\Models\SubscriptionDependencyPackage;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SubscriptionDependencyPackageFactory.
 * @method SubscriptionDependencyPackage create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionDependencyPackageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionDependencyPackage::class;

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
