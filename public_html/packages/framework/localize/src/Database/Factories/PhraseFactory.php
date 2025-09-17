<?php

namespace MetaFox\Localize\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PhraseFactory.
 * @method Phrase create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class PhraseFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Phrase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text  = $this->faker->sentence;
        $group = 'phpunit';

        $name = strtolower(str_replace(' ', '_', $text));

        return [
            'key'   => 'core::phrase.' . $name,
            'name'  => $name,
            'text'  => $text,
            'group' => $group,
        ];
    }
}

// end
