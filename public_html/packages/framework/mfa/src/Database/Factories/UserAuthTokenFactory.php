<?php

namespace MetaFox\Mfa\Database\Factories;

use MetaFox\Platform\Support\Factory\HasSetState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Mfa\Models\UserAuthToken;

/**
 * stub: /packages/database/factory.stub.
 */

/**
 * Class UserAuthTokenFactory.
 * @method UserAuthToken create($attributes = [], ?Model $parent = null)
 * @ignore
 */
class UserAuthTokenFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserAuthToken::class;

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
