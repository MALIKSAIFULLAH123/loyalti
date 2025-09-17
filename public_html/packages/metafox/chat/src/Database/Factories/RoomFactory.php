<?php

namespace MetaFox\Chat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Chat\Models\Room;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class RoomFactory
 * @method Room create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class RoomFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->name,
            'type'        => 'd',
            'user_id'     => 1,
            'user_type'   => 'user',
            'owner_id'    => 1,
            'owner_type'  => 'user',
            'is_archived' => 0,
            'is_readonly' => 0,
        ];
    }
}

// end
