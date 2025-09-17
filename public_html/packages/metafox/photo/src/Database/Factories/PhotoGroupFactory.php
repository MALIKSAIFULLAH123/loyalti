<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method PhotoGroup create($attributes = [], ?Model $parent = null)
 */
class PhotoGroupFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhotoGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'album_id'    => 0,
            'total_item' => 0,
        ];
    }
}
