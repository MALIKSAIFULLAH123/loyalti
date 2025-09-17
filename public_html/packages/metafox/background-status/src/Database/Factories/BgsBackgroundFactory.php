<?php

namespace MetaFox\BackgroundStatus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * @method BgsBackground create($attributes = [], ?Model $parent = null)
 */
class BgsBackgroundFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BgsBackground::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => 0,
            'image_path'    => $this->faker->imageUrl,
            'server_id'     => 'public',
            'is_deleted'    => 0,
            'ordering'      => 0,
        ];
    }

    public function setCollectionId(int $collectionId): self
    {
        return $this->state(function () use ($collectionId) {
            return [
                'collection_id' => $collectionId,
            ];
        });
    }
}

// end
