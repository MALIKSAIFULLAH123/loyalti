<?php

namespace MetaFox\Advertise\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Advertise\Models\InvoiceTransaction;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class InvoiceTransactionFactory.
 * @method InvoiceTransaction create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class InvoiceTransactionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceTransaction::class;

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
