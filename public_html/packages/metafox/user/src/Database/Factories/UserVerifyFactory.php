<?php

namespace MetaFox\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\User\Models\UserVerify;

/**
 * Class UserVerifyFactory.
 * @method UserVerify create($attributes = [], ?Model $parent = null)
 * @codeCoverageIgnore
 * @ignore
 */
class UserVerifyFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserVerify::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'verifiable' => 'example@example.com',
            'hash_code'  => 'example code',
            'user_id'    => 1,
            'user_type'  => 'user',
        ];
    }
}

// end
