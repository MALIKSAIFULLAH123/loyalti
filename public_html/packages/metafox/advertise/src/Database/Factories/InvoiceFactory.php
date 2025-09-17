<?php

namespace MetaFox\Advertise\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Advertise\Models\Invoice;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class InvoiceFactory.
 * @method Invoice create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class InvoiceFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

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
