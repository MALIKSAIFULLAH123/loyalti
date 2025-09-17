<?php

namespace MetaFox\Subscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Subscription\Models\SubscriptionComparison;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class SubscriptionComparisonFactory.
 * @method SubscriptionComparison create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class SubscriptionComparisonFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionComparison::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
        ];
    }

    public function setUser(User $user): static
    {
        return $this;
    }

    public function setOwner(User $user)
    {
        return $this;
    }
}

// end
