<?php

namespace MetaFox\Music\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Music\Models\Song;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class SongFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class SongFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Song::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'view_id'       => random_value(6, 1, 0),
            'explicit'      => random_value(6, 1, 0),
            'genre_id'      => mt_rand(1, 21),
            'album_id'      => 0,
            'user_id'       => null,
            'user_type'     => null,
            'owner_id'      => null,
            'owner_type'    => null,
            'module_id'     => 'music',
            'privacy'       => random_privacy(),
            'duration'      => mt_rand(200, 1000),
            'song_file_id'  => $this->sampleFile('mp3')?->id,
            'image_file_id' => $this->sampleFile('photo', '*', 80)?->id,
            'name'          => $this->faker->sentence,
            'description'   => $this->faker->paragraph(mt_rand(1, 3)),
            'created_at'    => $this->faker->dateTime,
            'updated_at'    => $this->faker->dateTime,
        ];
    }
}
