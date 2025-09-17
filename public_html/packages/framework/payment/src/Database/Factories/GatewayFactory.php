<?php

namespace MetaFox\Payment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Payment\Models\Gateway;
use StdClass;

/**
 * Class GatewayFactory.
 * @method Gateway create($attributes = [], ?Model $parent = null)
 */
class GatewayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Gateway::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $setting = new StdClass();
        $setting->key = $this->faker->name;

        return [
            'service'       => $this->faker->userName,
            'is_active'     => 0,
            'is_test'       => 1,
            'title'         => $this->faker->sentence,
            'description'   => $this->faker->sentence(3),
            'config'        => $setting,
            'service_class' => 'Class',
        ];
    }
}
