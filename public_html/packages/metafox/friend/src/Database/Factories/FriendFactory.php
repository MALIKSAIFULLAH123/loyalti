<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\Friend;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FriendFactory.
 * @method Friend create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Friend::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_id'   => 2,
            'owner_type' => 'user',
        ];
    }

    public function seed()
    {
        return $this->state(function ($attributes) {
            return [
                'owner_id' => $this->pickOtherUserId($attributes['user_id'], Friend::class),
            ];
        })->afterCreating(function ($friend) {
            Friend::query()->insertOrIgnore([
                'user_id'    => $friend->owner_id,
                'user_type'  => 'user',
                'owner_id'   => $friend->user_id,
                'owner_type' => 'user',
                'created_at' => $friend->created_at,
                'updated_at' => $friend->updated_at,
            ]);
        });
    }
}
