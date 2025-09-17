<?php

namespace MetaFox\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Core\Models\SiteSetting;

/**
 * Class SiteSettingFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class SiteSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SiteSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'module_id'     => 'test',
            'name'          => 'test.' . $this->faker->uuid,
            'is_auto'       => rand(0, 1),
            'value_actual'  => null,
            'value_default' => $this->faker->randomNumber(),
        ];
    }

    public function setSetting(string $moduleId, string $name)
    {
        return $this->state(function () use ($moduleId, $name) {
            return [
                'module_id' => $moduleId,
                'name'      => $moduleId . '.' . $name,
            ];
        });
    }

    public function setValue($valueDefault, $valueActual = null)
    {
        return $this->state(function () use ($valueDefault, $valueActual) {
            return [
                'value_default' => $valueDefault,
                'value_actual'  => $valueActual,
            ];
        });
    }

    public function setAuto(bool $value)
    {
        return $this->state(function () use ($value) {
            return [
                'is_auto' => $value,
            ];
        });
    }
}
