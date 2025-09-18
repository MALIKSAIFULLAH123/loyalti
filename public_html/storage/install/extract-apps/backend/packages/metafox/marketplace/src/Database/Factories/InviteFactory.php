<?php

namespace MetaFox\Marketplace\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Marketplace\Models\Invite;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class InviteFactory.
 * @method Invite create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
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
     * @return array
     */
    public function definition()
    {
        return [
            'listing_id' => null,
            'type_id'    => mt_rand(0, 1),
            'visited_id' => 0,
            'email'      => null,
        ];
    }
}
