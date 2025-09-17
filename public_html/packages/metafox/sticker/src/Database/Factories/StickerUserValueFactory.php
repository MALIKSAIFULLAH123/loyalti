<?php

namespace MetaFox\Sticker\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Sticker\Models\StickerUserValue;

/**
 * Class StickerUserValueFactory.
 * @ignore
 * @codeCoverageIgnore
 */
class StickerUserValueFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StickerUserValue::class;

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

    public function setStickerSetId(int $stickerSetId): self
    {
        return $this->state(function () use ($stickerSetId) {
            return [
                'set_id' => $stickerSetId,
            ];
        });
    }
}
