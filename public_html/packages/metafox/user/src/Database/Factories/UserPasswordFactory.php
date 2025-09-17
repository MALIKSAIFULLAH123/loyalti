<?php

namespace MetaFox\User\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\User\Models\UserPassword;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class UserPasswordFactory.
 * @method UserPassword create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class UserPasswordFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPassword::class;

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
