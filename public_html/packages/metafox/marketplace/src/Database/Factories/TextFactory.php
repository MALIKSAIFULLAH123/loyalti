<?php

namespace MetaFox\Marketplace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Marketplace\Models\Text;

/**
 * Class TextFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class TextFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Text::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'text'        => $this->faker->text,
            'text_parsed' => $this->faker->text,
        ];
    }
}
