<?php

namespace MetaFox\Chat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class SubscriptionFactory
 * @method Subscription create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id'      => 1,
            'user_id'      => 1,
            'user_type'    => 'user',
            'name'         => $this->faker->name,
            'is_favourite' => 0,
        ];
    }
}

// end
