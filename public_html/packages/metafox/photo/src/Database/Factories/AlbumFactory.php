<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AlbumFactory.
 * @method Album create($attributes = [], ?Model $parent = null)
 */
class AlbumFactory extends Factory
{
    use HasSetState;

    /** @var string */
    protected $model = Album::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id'       => 'photo',
            'user_id'         => 1,
            'user_type'       => 'user',
            'owner_id'        => 1,
            'owner_type'      => 'user',
            'privacy'         => mt_rand(0, 3),
            'is_featured'     => random_value(5, 1, 0),
            'is_sponsor'      => random_value(5, 1, 0),
            'is_approved'     => random_value(5, 1, 0),
            'sponsor_in_feed' => 0,
            'name'            => $this->faker->name,
            'album_type'      => 0,
            'cover_photo_id'  => 0,
        ];
    }
}
