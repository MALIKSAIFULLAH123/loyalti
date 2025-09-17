<?php

namespace MetaFox\Music\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Music\Models\Album;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AlbumFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Album::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $text = $this->faker->paragraph(1, 10);

        return [
            'view_id'         => random_value(5, 0, 1),
            'privacy'         => random_privacy(),
            'is_featured'     => random_value(5, 1, 0),
            'is_sponsor'      => random_value(5, 1, 0),
            'sponsor_in_feed' => random_value(5, 1, 0),
            'name'            => $this->faker->name,
            'year'            => mt_rand(0, 1) ? $this->faker->year : null,
            'module_id'       => 'music',
            'user_id'         => null,
            'user_type'       => null,
            'owner_id'        => null,
            'owner_type'      => null,
            'created_at'      => $this->faker->dateTime,
            'updated_at'      => $this->faker->dateTime,
            'text'            => $text,
        ];
    }
}
