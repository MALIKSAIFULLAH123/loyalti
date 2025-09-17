<?php

namespace MetaFox\Activity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Activity\Models\Subscription;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class SubscriptionFactory.
 * @codeCoverageIgnore
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
            'user_id'   => 1,
            'owner_id'  => 1,
            'is_active' => true,
        ];
    }
}
