<?php

namespace MetaFox\Friend\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendTagBlocked;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class FriendTagBlockedFactory.
 * @method FriendTagBlocked create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class FriendTagBlockedFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendTagBlocked::class;

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
