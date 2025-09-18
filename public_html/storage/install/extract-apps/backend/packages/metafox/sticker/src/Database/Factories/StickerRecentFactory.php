<?php

namespace MetaFox\Sticker\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Sticker\Models\StickerRecent;

/**
 * Class StickerRecentFactory.
 * @ignore
 * @codeCoverageIgnore
 * @method StickerRecent create($attributes = [], ?Model $parent = null)
 */
class StickerRecentFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StickerRecent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

// end
