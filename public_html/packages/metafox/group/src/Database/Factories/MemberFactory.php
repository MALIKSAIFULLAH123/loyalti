<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class MemberFactory.
 *
 * @method Member create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class MemberFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_type' => Member::MEMBER,
            'group_id'    => 1,
            'user_id'     => $this->randomUserId(),
            'user_type'   => 'user',
            'created_at'  => $this->faker->dateTimeBetween('-5 months', 'now'),
        ];
    }

    /**
     * @param User $group
     *
     * @return self
     */
    public function setOwner(User $group)
    {
        return $this->state(function () use ($group) {
            return [
                'group_id' => $group->entityId(),
            ];
        });
    }

    /**
     * @return self
     */
    public function setAdmin(): self
    {
        return $this->state(function () {
            return [
                'member_type' => Member::ADMIN,
            ];
        });
    }

    /**
     * @return self
     */
    public function setModerator(): self
    {
        return $this->state(function () {
            return [
                'member_type' => Member::MODERATOR,
            ];
        });
    }

    public function seed()
    {
        return $this->state(function ($attributes) {
            $id = Group::query()->select(['id'])
                ->where('user_id', '<>', $attributes['user_id'])
                ->inRandomOrder()
                ->value('id');

            return [
                'member_type' => Member::MODERATOR,
                'group_id'    => $id,
            ];
        });
    }
}
