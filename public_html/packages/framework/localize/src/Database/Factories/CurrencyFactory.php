<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\Currency;

/**
 * Class CurrencyFactory.
 * @method Currency create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'       => $this->faker->regexify('[A-Z]{3}'),
            'symbol'     => 'symbol',
            'name'       => $this->faker->sentence(2),
            'format'     => '[{0} #,###.00 {1}]',
            'is_active'  => 0,
            'is_default' => 0,
            'ordering'   => 0,
        ];
    }
}
