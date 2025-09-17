<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class FriendRequestFactory.
 * @method FriendRequest create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRequestFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_type' => 'user',
            'owner_id'   => null,
            'status_id'  => 1,
            'is_deny'    => 0,
        ];
    }

    public function seed()
    {
        return $this->state(function ($attrs) {
            return [
                'owner_type' => 'user',
                'owner_id'   => $this->pickOtherUserId($attrs['user_id'], FriendRequest::class),
            ];
        })->afterCreating(function ($arg) {
        });
    }
}

// end
