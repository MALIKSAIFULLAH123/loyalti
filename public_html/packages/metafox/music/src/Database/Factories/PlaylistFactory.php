<?php

namespace MetaFox\Music\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Music\Models\Playlist;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PlaylistFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Playlist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'     => null,
            'user_type'   => null,
            'owner_id'    => null,
            'owner_type'  => null,
            'name'        => $this->faker->sentence,
            'description' => $this->faker->paragraph(mt_rand(1, 3)),
            'is_active'   => random_value(5, 0, 1),
            'privacy'     => 0,
            'created_at'  => $this->faker->dateTime,
            'updated_at'  => $this->faker->dateTime,
        ];
    }
}
