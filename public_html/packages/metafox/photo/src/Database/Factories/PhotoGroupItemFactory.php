<?php

namespace MetaFox\Photo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method PhotoGroupItem create($attributes = [], ?Model $parent = null)
 */
class PhotoGroupItemFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhotoGroupItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => 0,
        ];
    }
}
