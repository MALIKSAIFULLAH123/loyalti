<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\UserBan;

/**
 * Class UserBanFactory.
 * @packge MetaFox\User\Database\Factories
 * @codeCoverageIgnore
 * @ignore
 */
class UserBanFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserBan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_user_group' => UserRole::NORMAL_USER_ID,
            'reason'            => $this->faker->word,
            'start_time_stamp'  => 0,
            'end_time_stamp'    => 0,
        ];
    }

    /**
     * @param int $startTime
     * @param int $endTime
     *
     * @return self
     */
    public function setTime(int $startTime, int $endTime = 0): self
    {
        return $this->state(function () use ($startTime, $endTime) {
            return [
                'start_time_stamp' => $startTime,
                'end_time_stamp'   => $endTime,
            ];
        });
    }
}

// end
