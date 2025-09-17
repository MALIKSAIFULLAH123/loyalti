<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\Language;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @ignore
 * @method Language create($attributes = [], ?Model $parent = null)
 * @codeCoverageIgnore
 */
class LanguageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_code' => $this->faker->languageCode,
            'name'          => $this->faker->title,
            'charset'       => 'utf-8',
            'direction'     => 'ltr',
            'is_default'    => 0,
            'is_active'     => 1,
            'is_master'     => 1,
            'store_id'      => 0,
        ];
    }
}

// end
