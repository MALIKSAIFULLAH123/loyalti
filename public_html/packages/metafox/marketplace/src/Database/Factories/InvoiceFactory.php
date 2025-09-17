<?php

namespace MetaFox\Marketplace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class InvoiceFactory.
 * @ignore
 * @codeCoverageIgnore
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
     * @return array
     */
    public function definition()
    {
        return [
            'listing_id' => null,
            'type_id'    => null,
            'visited_id' => null,
            'user_id'    => null,
            'user_type'  => null,
            'price'      => $this->faker->numberBetween(0, 1000),
            'paid_at'    => $this->faker->dateTime,
            'status'     => null,
        ];
    }
}
