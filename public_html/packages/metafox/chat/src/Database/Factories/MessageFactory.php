<?php

namespace MetaFox\Chat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Chat\Models\Message;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class MessageFactory
 * @method Message create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class MessageFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id'   => 1,
            'user_id'   => 1,
            'user_type' => 'user',
            'type'      => 'message',
            'message'   => $this->faker->paragraph,
        ];
    }
}

// end
