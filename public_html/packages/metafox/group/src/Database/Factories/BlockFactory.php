<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Block;
use MetaFox\Group\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub
 */

/**
 * Class BlockFactory
 * @method Block create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class BlockFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_type' => Member::MEMBER,
        ];
    }

    /**
     * @param  User  $group
     * @return $this
     */
    public function setOwner(User $group): self
    {
        return $this->state(function () use ($group) {
            return [
                'group_id' => $group->entityId(),
            ];
        });
    }

    /**
     * @return $this
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
     * @return $this
     */
    public function setModerator(): self
    {
        return $this->state(function () {
            return [
                'member_type' => Member::MODERATOR,
            ];
        });
    }
}

// end
