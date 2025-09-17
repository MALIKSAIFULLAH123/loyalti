<?php

namespace MetaFox\Invite\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Invite\Models\Invite;

/**
 * stub: /packages/database/factory.stub.
 */

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
            //
        ];
    }
}

// end
