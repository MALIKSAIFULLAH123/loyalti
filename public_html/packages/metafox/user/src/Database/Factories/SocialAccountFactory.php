<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\SocialAccount;

/**
 * Class SocialAccountFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 * @method SocialAccount create($attributes = [], ?Model $parent = null)
 */
class SocialAccountFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SocialAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_user_id' => $this->faker->numberBetween(99999, 999999),
            'provider'         => $this->faker->text,
        ];
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user)
    {
        return $this->state(function () use ($user) {
            return [
                'user_id' => $user->entityId(),
            ];
        });
    }
}

// end
