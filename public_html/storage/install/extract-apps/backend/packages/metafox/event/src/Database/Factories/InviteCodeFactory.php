<?php

namespace MetaFox\Event\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class InviteCodeFactory.
 * @method InviteCode create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class InviteCodeFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InviteCode::class;

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
