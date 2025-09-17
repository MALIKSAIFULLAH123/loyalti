<?php

namespace MetaFox\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Subscription\Models\SubscriptionInvoice;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SubscriptionInvoiceFactory.
 * @method SubscriptionInvoice create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionInvoiceFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => 1,
            'currency'   => 'USD',
        ];
    }

    public function setOwner(User $user)
    {
        return $this;
    }
}

// end
