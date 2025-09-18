<?php

namespace MetaFox\Group\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class InviteFactory.
 * @method Invite create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class InviteFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id'   => Group::all()->random()->id,
            'user_id'    => 1,
            'user_type'  => 'user',
            'owner_id'   => 2,
            'owner_type' => 2,
            'expired_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'code'       => $this->faker->sha1(),
        ];
    }
}

// end
