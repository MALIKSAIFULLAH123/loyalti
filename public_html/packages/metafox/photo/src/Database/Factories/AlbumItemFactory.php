<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method AlbumItem create($attributes = [], ?Model $parent = null)
 */
class AlbumItemFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AlbumItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => 0,
        ];
    }
}
