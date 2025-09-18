<?php

namespace MetaFox\Sticker\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Sticker\Models\StickerSet;

/**
 * Class StickerSetFactory.
 *
 * @method StickerSet create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class StickerSetFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StickerSet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'         => $this->faker->sentence,
            'total_sticker' => 0,
            'is_default'    => 0,
            'thumbnail_id'  => 0,
            'is_active'     => 1,
            'ordering'      => 0,
            'view_only'     => 0,
            'is_deleted'    => 0,
        ];
    }
}

// end
