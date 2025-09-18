<?php

namespace MetaFox\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Subscription\Models\SubscriptionInvoiceTransaction;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SubscriptionInvoiceTransactionFactory.
 * @method SubscriptionInvoiceTransaction create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionInvoiceTransactionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionInvoiceTransaction::class;

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
