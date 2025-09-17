<?php

namespace MetaFox\Like\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class LikeFactory.
 * @method Like create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class LikeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reaction_id' => mt_rand(1, 6),
            'user_id'     => $this->randomUserId(),
            'user_type'   => 'user',
            'owner_id'    => 1,
            'owner_type'  => 'user',
        ];
    }

    public function setItem(Content $item): self
    {
        return $this->state(function () use ($item) {
            return [
                'item_id'    => $item->entityId(),
                'item_type'  => $item->entityType(),
                'owner_id'   => $item->ownerId(),
                'owner_type' => $item->ownerType(),
            ];
        });
    }
}
