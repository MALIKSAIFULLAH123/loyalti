<?php

namespace MetaFox\Sticker\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Sticker\Models\Sticker;

/**
 * Class StickerFactory.
 *
 * @method Sticker create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class StickerFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sticker::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_path' => $this->faker->imageUrl,
            'server_id'  => 'public',
            'ordering'   => 0,
            'view_only'  => 0,
            'is_deleted' => 0,
        ];
    }

    public function setStickerSetId(int $stickerSetId): self
    {
        return $this->state(function () use ($stickerSetId) {
            return [
                'set_id' => $stickerSetId,
            ];
        });
    }
}
